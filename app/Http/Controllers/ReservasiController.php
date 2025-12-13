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
use App\Models\JadwalHarian;
use App\Models\DataPasien;
use App\Models\TransaksiBayar;
use Carbon\Carbon;
use Exception;

class ReservasiController extends Controller
{
    // ... (Helper & UserData tidak berubah) ...
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

    // ============================================================
    // 🔥 GET JADWAL (DENGAN AUTO CANCEL)
    // ============================================================

    public function getJadwalDenganKuota(Request $request)
    {
        $request->validate([
            'kode_poli'         => 'required|string', 
            'kode_dokter'       => 'nullable|string',
            'tanggal_reservasi' => 'nullable|date_format:Y-m-d',
        ]);

        try {
            // --- 🔥 LOGIC BARU: AUTO CANCEL EXPIRED ---
            // Cari reservasi yg statusnya 'menunggu_pembayaran' DAN dibuat > 60 menit yang lalu
            $batasWaktu = Carbon::now()->subMinutes(60); // 1 Jam batas bayar

            Reservasi::where('status_pembayaran', 'menunggu_pembayaran')
                ->where('created_at', '<', $batasWaktu)
                ->update([
                    'status_reservasi'  => 'batal',
                    'status_pembayaran' => 'gagal', // Atau status 'expired' jika ada di enum
                    'status'            => 'Dibatalkan (Waktu Habis)'
                ]);
            // ------------------------------------------

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
                        // Hitung yang statusnya MASIH AKTIF saja
                        $kuotaTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                            ->where('tanggal_pesan', $tanggalReservasi)
                            ->whereIn('status_pembayaran', [
                                'menunggu_pembayaran',
                                'menunggu_verifikasi',
                                'terverifikasi'
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
                    'status_jadwal'  => $statusJadwal,
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
    // CREATE (TIDAK BERUBAH BANYAK, CUMA VALIDASI HARI)
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
                'metode_pembayaran' => 'required|string',
                'jenis_pasien'      => 'required|string|in:Umum,BPJS,Asuransi',
            ]);

            $pasien = RekamMedis::find($validated['rekam_medis_id']);
            $dokter = MasterDokter::where('kode_dokter', $validated['dokter_id'])->firstOrFail();
            $jadwal = MasterJadwal::find($validated['jadwal_id']);

            // Validasi Hari (Senin=1, dll)
            $hariPilihan = Carbon::parse($validated['tanggal_pesan'])->dayOfWeekIso;
            if ($hariPilihan != $jadwal->hari) {
                return $this->errorResponse("Jadwal tidak tersedia di hari tersebut.");
            }

            // Cek Libur
            $cekLibur = JadwalHarian::where('kode_jadwal', $jadwal->id)
                        ->where('tanggal', $validated['tanggal_pesan'])
                        ->where('validasi', 0)->exists();
            if ($cekLibur) return $this->errorResponse('Dokter sedang libur.');

            // Cek Kuota (Otomatis tidak menghitung yang sudah 'batal' karena logic di getJadwal tadi)
            $reservasiTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('tanggal_pesan', $validated['tanggal_pesan'])
                ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi'])
                ->count();

            if ($reservasiTerpakai >= $jadwal->quota) {
                DB::rollBack();
                return $this->errorResponse('Maaf, kuota penuh.', null, 409);
            }

            $noPemeriksaan = $this->generateNoPemeriksaan();
            $biaya = 25000; 

            $reservasi = Reservasi::create([
                'no_pemeriksaan'    => $noPemeriksaan,
                'no_antrian'        => null, 
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
            return $this->successResponse('Reservasi berhasil dibuat.', [
                'no_pemeriksaan'    => $reservasi->no_pemeriksaan,
                'no_antrian'        => '-', 
                'pasien_id'         => $pasien->rekam_medis,
                'nama_pasien'       => $pasien->nama_lengkap ?? 'Pasien',
                'nama_dokter'       => $dokter->nama,
                'tanggal_kunjungan' => $validated['tanggal_pesan'],
                'jam_layanan'       => $jadwal->jam_mulai . ' - ' . $jadwal->jam_selesai,
                'total_bayar'       => $reservasi->pembayaran_total,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Create Reservasi Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memproses reservasi', $e->getMessage(), 500); 
        }
    }

    // ... (updatePembayaran & riwayatReservasi TETAP SAMA, tidak perlu diubah) ...
    public function updatePembayaran(Request $request, $no_pemeriksaan) {
        // Copy dari yang sebelumnya saja, logika antriannya sudah benar
        DB::beginTransaction();
        try {
            $reservasi = Reservasi::where('no_pemeriksaan', $no_pemeriksaan)->first();
            if (!$reservasi) return $this->errorResponse('Tidak ditemukan', null, 404);

            // Logic Antrian (sama seperti sebelumnya)
            $maxAntrian = DataPasien::where('id_jadwal', $reservasi->jadwal_id)
                                    ->whereDate('created_at', Carbon::today())->max('no_antri'); 
            $urutanBaru = $maxAntrian ? ($maxAntrian + 1) : 1;
            
            $prefix = match ($reservasi->jenis_pasien) {
                'BPJS' => 'B', 'Asuransi' => 'A', default => 'U'
            };
            $noAntrianString = $prefix . '-' . str_pad($urutanBaru, 3, '0', STR_PAD_LEFT);

            $reservasi->update([
                'status_pembayaran' => 'terverifikasi',
                'status_reservasi'  => 'terkonfirmasi',
                'status'            => 'Menunggu Dokter',
                'no_antrian'        => $noAntrianString
            ]);

            // Insert DataPasien & Transaksi (Sama seperti sebelumnya)
            $cekAntrian = DataPasien::where('rekam_medis', $reservasi->pasien_id)
                            ->where('id_jadwal', $reservasi->jadwal_id)
                            ->whereDate('created_at', Carbon::today())->first();
            
            $idPeriksa = null;
            if (!$cekAntrian) {
                $dp = DataPasien::create([
                    'id_jadwal' => $reservasi->jadwal_id,
                    'rekam_medis' => $reservasi->pasien_id,
                    'no_antri' => $urutanBaru,
                    'status' => 1, 'pasien_baru' => 0, 'rujukan' => 0, 'biaya_admin' => 0,
                    'keluhan' => $reservasi->keluhan,
                ]);
                $idPeriksa = $dp->id;
            } else { $idPeriksa = $cekAntrian->id; }

            $cekTrx = TransaksiBayar::where('id_periksa', $idPeriksa)->first();
            if (!$cekTrx && $idPeriksa) {
                TransaksiBayar::create([
                    'id_periksa' => $idPeriksa, 'total_tindakan' => 0, 'total_obat' => 0,
                    'total_penunjang' => 0, 'total_tambahan' => 0, 'total_bayar' => 0, 
                    'waktu' => Carbon::now(), 'diskon' => 0, 'biaya_admin' => 0, 'pasien_baru' => 0,
                ]);
            }

            DB::commit();
            return $this->successResponse('Pembayaran Valid', ['reservasi' => $reservasi]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal update', $e->getMessage(), 500);
        }
    }

    public function riwayatReservasi($rekam_medis_id) {
        // Sama persis, tidak perlu diubah
        try {
            $data = Reservasi::where('pasien_id', $rekam_medis_id)
                ->with(['dokter', 'jadwal.poli'])
                ->orderBy('tanggal_pesan', 'desc')->get();
            return $this->successResponse('Riwayat ditemukan', $data);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal memuat riwayat', null, 500);
        }
    }
}