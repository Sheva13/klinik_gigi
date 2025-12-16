<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

// Dependency Injection Service
use App\Services\Payment\MidtransService; 

// Models
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;
use App\Models\MasterPoli;
use App\Models\JadwalHarian;

class ReservasiController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    // ==========================================================
    // HELPER RESPONSE
    // ==========================================================
    protected function successResponse($message, $data = null, $code = 200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    protected function errorResponse($message, $errors = null, $code = 400)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors
        ], $code);
    }

    // ==========================================================
    // 1. GET USER DATA
    // ==========================================================
    public function getUserData(Request $request)
    {
        $user = Auth::user();
        if (!$user) return $this->errorResponse('User belum login', null, 401);
        
        $rekamMedis = $user->rekamMedis;
        if (!$rekamMedis) return $this->errorResponse('Data rekam medis tidak ditemukan', null, 404);

        return $this->successResponse('Data user berhasil diambil', [
            'nama_lengkap'    => $rekamMedis->nama ?? $user->nama_pengguna, 
            'no_rekam_medis'  => $rekamMedis->rekam_medis ?? '-',
            'user_id'         => $user->user_id,
            'email'           => $user->email,
            'phone'           => $user->phone ?? $rekamMedis->telepon ?? null, 
        ]);
    }

    // Helper: Generate No Pemeriksaan (RSV-YYYYMMDDXXX)
    private function generateNoPemeriksaan()
    {
        $tanggal = Carbon::now()->format('Ymd');
        $prefix = "RSV-{$tanggal}";

        $lastReservasi = Reservasi::where('no_pemeriksaan', 'LIKE', $prefix . '%')
                                     ->whereDate('created_at', Carbon::today())
                                     ->orderBy('no_pemeriksaan', 'desc')
                                     ->first();
        
        $urutan = 1;
        if ($lastReservasi) {
            $lastNumber = (int) substr($lastReservasi->no_pemeriksaan, -3); 
            $urutan = $lastNumber + 1;
        }

        return $prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT); 
    }

    // ==========================================================
    // 2. DATA MASTER (POLI, DOKTER, JADWAL)
    // ==========================================================
    public function getDaftarPoli()
    {
        try {
            $poli = MasterPoli::select('kode_poli', 'nama_poli')->get();
            return $this->successResponse('Daftar poli berhasil diambil', $poli);
        } catch (Exception $e) {
            Log::error('Get Daftar Poli Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data poli', null, 500);
        }
    }

    public function getDokterByPoli(Request $request)
    {
        $request->validate(['kode_poli' => 'nullable|string']);
        try {
            $query = MasterDokter::select('kode_dokter', 'nama', 'gelar', 'kode_poli');
            
            if (!empty($request->kode_poli) && strtolower($request->kode_poli) !== 'semua') {
                $query->where('kode_poli', $request->kode_poli);
            }
            
            $dokter = $query->get();
            return $this->successResponse('Daftar dokter berhasil diambil', $dokter);
        } catch (Exception $e) {
            Log::error('Get Dokter Error: '.$e->getMessage());
            return $this->errorResponse('Gagal mengambil data dokter', null, 500);
        }
    }

    public function getJadwalDenganKuota(Request $request)
    {
        $request->validate([
            'kode_poli'         => 'required|string', 
            'kode_dokter'       => 'nullable|string',
            'tanggal_reservasi' => 'nullable|date_format:Y-m-d',
        ]);

        try {
            // Auto Cancel Expired (> 60 menit)
            $batasWaktu = Carbon::now()->subMinutes(60); 
            Reservasi::where('status_pembayaran', 'menunggu_pembayaran')
                ->where('created_at', '<', $batasWaktu)
                ->update([
                    'status_reservasi'  => 'batal',
                    'status_pembayaran' => 'gagal',
                    'status'            => 'Dibatalkan (Waktu Habis)'
                ]);

            $query = MasterJadwal::query();
            $kodePoli = $request->kode_poli;
            
            $query->where(function($q) use ($kodePoli) {
                $q->where('kode_poli', $kodePoli)
                  ->orWhereHas('dokter', function ($dq) use ($kodePoli) {
                      $dq->where('kode_poli', $kodePoli);
                  });
            });

            if ($request->filled('kode_dokter') && strtolower($request->kode_dokter) !== 'semua') {
                $query->where('kode_dokter', $request->kode_dokter);
            }

            $isDateSelected = $request->filled('tanggal_reservasi');
            $tanggalReservasi = $request->tanggal_reservasi;

            if ($isDateSelected) {
                $hariInggris = Carbon::parse($tanggalReservasi)->format('l');
                $hariMapping = [
                    'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
                    'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7,
                ];
                $query->where('hari', $hariMapping[$hariInggris]);
            }

            $jadwalList = $query->with(['dokter', 'poli'])->get();

            if ($jadwalList->isEmpty()) {
                return $this->successResponse('Jadwal tidak ditemukan.', []);
            }

            $hasil = $jadwalList->map(function ($jadwal) use ($isDateSelected, $tanggalReservasi) {
                $sisaKuota = $jadwal->quota;
                $kuotaTerpakai = 0;
                $statusJadwal = 'Tersedia';

                if ($isDateSelected) {
                    $cekLibur = JadwalHarian::where('kode_jadwal', $jadwal->id)
                                         ->where('tanggal', $tanggalReservasi)
                                         ->where('validasi', 0)
                                         ->exists();
                    
                    if ($cekLibur) {
                        $statusJadwal = 'Libur';
                        $sisaKuota = 0;
                    } else {
                        $kuotaTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                            ->where('tanggal_pesan', $tanggalReservasi)
                            ->whereIn('status_pembayaran', [
                                'menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'lunas'
                            ])
                            ->count();
                        
                        $sisaKuota = $jadwal->quota - $kuotaTerpakai;
                        if ($sisaKuota <= 0) $statusJadwal = 'Penuh';
                    }
                }

                $namaHari = match ($jadwal->hari) {
                    1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                    4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
                    default => '-'
                };

                return [
                    'jadwal_id'       => $jadwal->id,
                    'kode_dokter'     => $jadwal->kode_dokter,
                    'nama_dokter'     => $jadwal->dokter->nama ?? 'Dokter Umum',
                    'kode_poli'       => $jadwal->kode_poli,
                    'nama_poli'       => $jadwal->poli->nama_poli ?? '-',
                    'hari'            => $namaHari,
                    'jam_mulai'       => $jadwal->jam_mulai,
                    'jam_selesai'     => $jadwal->jam_selesai,
                    'kuota_total'     => $jadwal->quota,
                    'kuota_terpakai'  => $isDateSelected ? $kuotaTerpakai : 0,
                    'sisa_kuota'      => $sisaKuota,
                    'status_jadwal'   => $statusJadwal,
                    'tanggal_pilih'   => $isDateSelected ? $tanggalReservasi : null
                ];
            });

            return $this->successResponse('Data jadwal berhasil diambil', $hasil);
        } catch (Exception $e) {
            Log::error('Get Jadwal Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data jadwal', null, 500);
        }
    }

    // ============================================================
    // 3. INTI TRANSAKSI (CREATE RESERVASI)
    // ============================================================
    public function createReservasi(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'rekam_medis_id'    => 'required|exists:rekam_medis,id', 
                'dokter_id'         => 'required|string|exists:master_dokter,kode_dokter',
                'jadwal_id'         => 'required|integer|exists:master_jadwal,id',
                'tanggal_pesan'     => 'required|date_format:Y-m-d',
                'keluhan'           => 'nullable|string|max:100',
                'metode_pembayaran' => 'required|string|in:Midtrans,Umum',
                'jenis_pasien'      => 'required|string|in:Umum,BPJS,Asuransi',
                'user_name'         => 'required|string', 
                'user_email'        => 'required|email',
                'user_phone'        => 'required|string',
            ]);

            $pasien = RekamMedis::find($validated['rekam_medis_id']);
            $dokter = MasterDokter::where('kode_dokter', $validated['dokter_id'])->firstOrFail();
            $jadwal = MasterJadwal::find($validated['jadwal_id']);

            // Validasi Ketersediaan (Hari & Libur)
            $hariPilihan = Carbon::parse($validated['tanggal_pesan'])->dayOfWeekIso;
            if ($hariPilihan != $jadwal->hari) {
                return $this->errorResponse("Jadwal tidak tersedia di hari tersebut.");
            }
            $cekLibur = JadwalHarian::where('kode_jadwal', $jadwal->id)
                                    ->where('tanggal', $validated['tanggal_pesan'])
                                    ->where('validasi', 0)->exists();
            if ($cekLibur) return $this->errorResponse('Dokter sedang libur.');

            // Validasi Kuota
            $reservasiTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('tanggal_pesan', $validated['tanggal_pesan'])
                ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'lunas'])
                ->count();
            if ($reservasiTerpakai >= $jadwal->quota) {
                DB::rollBack();
                return $this->errorResponse('Maaf, kuota penuh.', null, 409);
            }

            // Logic Reservasi
            $noPemeriksaan = $this->generateNoPemeriksaan();
            $biaya = ($validated['metode_pembayaran'] === 'Midtrans') ? 25000 : 0;
            $statusPembayaranAwal = ($validated['metode_pembayaran'] === 'Midtrans') ? 'menunggu_pembayaran' : 'menunggu_verifikasi';

            $reservasi = Reservasi::create([
                'no_pemeriksaan'    => $noPemeriksaan,
                'no_antrian'        => null, // Diisi oleh Webhook
                'pasien_id'         => $pasien->rekam_medis,
                'dokter_id'         => $dokter->kode_dokter,
                'jadwal_id'         => $jadwal->id,
                'tanggal_pesan'     => $validated['tanggal_pesan'],
                'waktu_pesan'       => Carbon::now()->format('H:i:s'),
                'jam_mulai'         => $jadwal->jam_mulai,
                'jam_selesai'       => $jadwal->jam_selesai,
                'keluhan'           => $validated['keluhan'] ?? '-',
                'biaya_reservasi'   => $biaya,
                'pembayaran_total'  => $biaya,
                'status'            => 'Menunggu Pembayaran',
                'status_reservasi'  => 'menunggu',
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => $statusPembayaranAwal,
                'jenis_pasien'      => $validated['jenis_pasien'],
            ]);
            
            // Logic Midtrans (Snap URL)
            $redirectUrl = null; 

            if ($validated['metode_pembayaran'] === 'Midtrans') {
                $redirectUrl = $this->midtransService->getSnapUrl(
                    $reservasi->no_pemeriksaan, 
                    $reservasi->pembayaran_total,
                    $validated['user_name'],
                    $validated['user_email'], 
                    $validated['user_phone'],
                    [[
                        'id' => 'RES-'.$reservasi->id,
                        'price' => $reservasi->pembayaran_total,
                        'quantity' => 1,
                        'name' => 'Biaya Reservasi Dokter'
                    ]]
                );
            }

            DB::commit();
            
            return $this->successResponse('Reservasi berhasil dibuat.', [
                'no_pemeriksaan'    => $reservasi->no_pemeriksaan,
                'total_bayar'       => number_format($reservasi->pembayaran_total, 0, ',', '.'), 
                'redirect_url'      => $redirectUrl, // Mengirim URL ke Flutter
                'metode_pembayaran' => $reservasi->metode_pembayaran,
                'status_pembayaran' => $reservasi->status_pembayaran,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Create Reservasi Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memproses reservasi: ' . $e->getMessage(), null, 500); 
        }
    }

    // ============================================================
    // 4. CEK STATUS PEMBAYARAN (POLLING)
    // ============================================================
    public function cekStatusPembayaran($no_pemeriksaan)
    {
        try {
            $reservasi = Reservasi::where('no_pemeriksaan', $no_pemeriksaan)->first();
            if (!$reservasi) return $this->errorResponse('Order ID tidak ditemukan', null, 404);
            
            $status = $reservasi->status_pembayaran; 
            
            return $this->successResponse('Status pembayaran berhasil diambil', [
                'no_pemeriksaan'    => $no_pemeriksaan,
                'status_pembayaran' => $status, 
                'is_lunas'          => ($status === 'lunas' || $status === 'terverifikasi'),
                'is_pending'        => ($status === 'menunggu_pembayaran'),
                'is_failed'         => ($status === 'gagal'),
                'detail_status'     => $reservasi->status,
                'total_bayar'       => number_format($reservasi->pembayaran_total, 0, ',', '.'), 
                'no_antrian'        => $reservasi->no_antrian ?? '-', 
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal mengambil status', null, 500); 
        }
    }

    // ============================================================
    // 5. UPDATE PEMBAYARAN (MANUAL / FALLBACK)
    // ============================================================
    public function updatePembayaran(Request $request, $no_pemeriksaan) {
        DB::beginTransaction();
        try {
            $reservasi = Reservasi::where('no_pemeriksaan', $no_pemeriksaan)->first();
            if (!$reservasi) return $this->errorResponse('Tidak ditemukan', null, 404);

            // Hanya update jika masih pending, logic antrian ada di Webhook
            if ($reservasi->status_pembayaran === 'menunggu_pembayaran') {
                $reservasi->update([
                    'status_pembayaran' => 'lunas', 
                    'status_reservasi'  => 'terkonfirmasi',
                    'status'            => 'Menunggu Dokter',
                ]);
            }

            DB::commit();
            return $this->successResponse('Pembayaran Valid', ['reservasi' => $reservasi]); 
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal update', $e->getMessage(), 500);
        }
    }

    // ============================================================
    // 6. RIWAYAT RESERVASI
    // ============================================================
    public function riwayatReservasi($rekam_medis_id) {
        try {
            $data = Reservasi::where('pasien_id', $rekam_medis_id)
                ->with(['dokter', 'jadwal.poli'])
                ->orderBy('tanggal_pesan', 'desc') 
                ->orderBy('waktu_pesan', 'desc')
                ->get();
            return $this->successResponse('Riwayat ditemukan', $data);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal memuat riwayat', null, 500);
        }
    }
}