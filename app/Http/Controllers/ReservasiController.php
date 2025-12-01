<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;
use App\Models\MasterPoli;
use App\Models\MpUser;
use Carbon\Carbon;
use Exception;

class ReservasiController extends Controller
{
    // ============================================================
    // HELPER RESPONSE
    // ============================================================
    
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

    // ============================================================
    // USER DATA & UTILS
    // ============================================================

    public function getUserData(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('User belum login atau tidak ditemukan', null, 401);
        }

        $rekamMedis = $user->rekamMedis;

        if (!$rekamMedis) {
            return $this->errorResponse('Data rekam medis tidak ditemukan', null, 404);
        }

        return $this->successResponse('Data user berhasil diambil', [
            'nama_lengkap'    => $rekamMedis->nama ?? $user->nama_pengguna, 
            'no_rekam_medis'  => $rekamMedis->rekam_medis ?? '-',
            'user_id'         => $user->user_id,
            'email'           => $user->email,
        ]);
    }

    private function generateNoPemeriksaan()
    {
        $tanggal = Carbon::now()->format('Ymd');
        do {
            $random = random_int(100, 999);
            $noPemeriksaan = "RSV-{$tanggal}{$random}";
        } while (Reservasi::where('no_pemeriksaan', $noPemeriksaan)->exists());

        return $noPemeriksaan;
    }

    // ============================================================
    // ALUR AWAL (PILIH POLI → DOKTER → JADWAL)
    // ============================================================

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
        $request->validate([
            'kode_poli' => 'nullable|string'
        ]);

        try {
            if (empty($request->kode_poli) || strtolower($request->kode_poli) === 'semua') {
                $dokter = MasterDokter::select('kode_dokter', 'nama', 'gelar', 'kode_poli')->get();
            } else {
                $dokter = MasterDokter::where('kode_poli', $request->kode_poli)
                            ->select('kode_dokter', 'nama', 'gelar', 'kode_poli')
                            ->get();
            }

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
                $kodeHari = $hariMapping[$hariInggris];

                $query->where('hari', $kodeHari);
            }

            $jadwalList = $query->with(['dokter', 'poli'])->get();

            if ($jadwalList->isEmpty()) {
                return $this->successResponse('Jadwal tidak ditemukan untuk kriteria ini.', []);
            }

            $hasil = $jadwalList->map(function ($jadwal) use ($isDateSelected, $tanggalReservasi) {
                
                $sisaKuota = $jadwal->quota;
                $kuotaTerpakai = 0;

                if ($isDateSelected) {
                    $kuotaTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                        ->where('tanggal_pesan', $tanggalReservasi)
                        ->whereIn('status_pembayaran', [
                            'menunggu_pembayaran',
                            'menunggu_verifikasi',
                            'terverifikasi'
                        ])
                        ->count();
                    
                    $sisaKuota = $jadwal->quota - $kuotaTerpakai;
                }

                $namaHari = match ($jadwal->hari) {
                    1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                    4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
                    default => 'Hari tidak valid'
                };

                return [
                    'jadwal_id'      => $jadwal->id,
                    'kode_dokter'    => $jadwal->kode_dokter,
                    'nama_dokter'    => $jadwal->dokter->nama ?? 'Dokter Umum',
                    'kode_poli'      => $jadwal->kode_poli,
                    'nama_poli'      => $jadwal->poli->nama_poli ?? '-',
                    'hari'           => $namaHari,
                    'jam_mulai'      => $jadwal->jam_mulai,
                    'jam_selesai'    => $jadwal->jam_selesai,
                    'kuota_total'    => $jadwal->quota,
                    'kuota_terpakai' => $isDateSelected ? $kuotaTerpakai : 0,
                    'sisa_kuota'     => $sisaKuota,
                    'status_jadwal'  => ($sisaKuota <= 0 && $isDateSelected) ? 'Penuh' : 'Tersedia',
                    'tanggal_pilih'  => $isDateSelected ? $tanggalReservasi : null
                ];
            });

            return $this->successResponse('Data jadwal berhasil diambil', $hasil);

        } catch (Exception $e) {
            Log::error('Get Jadwal Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data jadwal', null, 500);
        }
    }

    // ============================================================
    // PROSES CREATE RESERVASI
    // ============================================================

    public function createReservasi(Request $request)
    {
        DB::beginTransaction();

        try {
            // 1. Validasi Input
            $validated = $request->validate([
                'rekam_medis_id'    => 'required|exists:rekam_medis,id', 
                'dokter_id'         => 'required|string|exists:master_dokter,kode_dokter',
                'jadwal_id'         => 'required|integer|exists:master_jadwal,id',
                'tanggal_pesan'     => 'required|date_format:Y-m-d',
                'keluhan'           => 'nullable|string|max:100',
                'metode_pembayaran' => 'required|string',
                'jenis_pasien'      => 'required|string|in:Umum,BPJS,Asuransi',
            ]);

            // 2. Ambil Data Relasi
            $pasien = RekamMedis::find($validated['rekam_medis_id']);
            
            if (!$pasien) {
                return $this->errorResponse('Data pasien tidak ditemukan.', null, 404);
            }

            $dokter = MasterDokter::where('kode_dokter', $validated['dokter_id'])->firstOrFail();
            $jadwal = MasterJadwal::find($validated['jadwal_id']);

            // 3. Cek Kuota Penuh
            $reservasiTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('tanggal_pesan', $validated['tanggal_pesan'])
                ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi'])
                ->count();

            if ($reservasiTerpakai >= $jadwal->quota) {
                DB::rollBack();
                return $this->errorResponse('Maaf, kuota untuk jadwal ini sudah penuh.', null, 409);
            }

            // ============================================================
            // GENERATE NO ANTRIAN
            // ============================================================
            
            $prefix = 'U'; 
            if ($validated['jenis_pasien'] == 'BPJS') {
                $prefix = 'B';
            } elseif ($validated['jenis_pasien'] == 'Asuransi') {
                $prefix = 'A';
            }

            $urutan = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('tanggal_pesan', $validated['tanggal_pesan'])
                ->where('jenis_pasien', $validated['jenis_pasien']) 
                ->count();

            $nomorUrut = $urutan + 1;
            $noAntrianString = $prefix . '-' . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT); 

            // 4. Generate No Pemeriksaan
            $noPemeriksaan = $this->generateNoPemeriksaan();
            $biaya = 25000; 

            // 5. Simpan ke Database
            $reservasi = Reservasi::create([
                'no_pemeriksaan'    => $noPemeriksaan,
                'no_antrian'        => $noAntrianString, 
                // 🛠️ Perbaikan Disini: Simpan Kode Aslinya (RM002), bukan ID (2)
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
                'status_pembayaran' => 'menunggu_pembayaran',
                'jenis_pasien'      => $validated['jenis_pasien'],
            ]);

            DB::commit();

            return $this->successResponse('Reservasi berhasil dibuat. Menunggu pembayaran.', [
                'no_pemeriksaan'    => $reservasi->no_pemeriksaan,
                'no_antrian'        => $reservasi->no_antrian, 
                'pasien_id'         => $pasien->rekam_medis,
                'nama_pasien'       => $pasien->nama_lengkap ?? 'Pasien',
                'nama_dokter'       => $dokter->nama,
                'spesialisasi'      => $dokter->spesialisasi,
                'tanggal_kunjungan' => $validated['tanggal_pesan'],
                'jam_layanan'       => $jadwal->jam_mulai . ' - ' . $jadwal->jam_selesai,
                'metode_bayar'      => $reservasi->metode_pembayaran,
                'total_bayar'       => $reservasi->pembayaran_total,
                'info_status'       => $reservasi->status,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse('Validasi data gagal', $e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Reservasi Create Error', ['user' => Auth::id(), 'error' => $e->getMessage()]);
            return $this->errorResponse('Terjadi kesalahan server saat memproses reservasi', $e->getMessage(), 500); 
        }
    }

    // ============================================================
    // UPDATE PEMBAYARAN
    // ============================================================

    public function updatePembayaran(Request $request, $no_pemeriksaan)
    {
        try {
            $reservasi = Reservasi::where('no_pemeriksaan', $no_pemeriksaan)->first();

            if (!$reservasi) {
                return $this->errorResponse('Reservasi tidak ditemukan', null, 404);
            }

            $reservasi->update([
                'status_pembayaran' => 'terverifikasi',
                'status_reservasi'  => 'menunggu',
                'status'            => 'Menunggu Dokter',
            ]);

            return $this->successResponse('Pembayaran berhasil diverifikasi. Silakan datang sesuai jadwal.', $reservasi);
            
        } catch (Exception $e) {
            Log::error('Update Pembayaran Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal update pembayaran', $e->getMessage(), 500);
        }
    }

    // ============================================================
    // RIWAYAT RESERVASI
    // ============================================================

    public function riwayatReservasi($rekam_medis_id)
    {
        try {
            $data = Reservasi::where('pasien_id', $rekam_medis_id)
                ->with(['dokter', 'jadwal.poli'])
                ->orderBy('tanggal_pesan', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($data->isEmpty()) {
                return $this->successResponse('Belum ada riwayat reservasi', []);
            }

            return $this->successResponse('Riwayat reservasi ditemukan', $data);

        } catch (Exception $e) {
            Log::error('Riwayat Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat riwayat', null, 500);
        }
    }
}