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
    // Helper Response
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

    /**
     * 🔹 Ambil data pasien yang sedang login
     * (untuk menampilkan nama & no rekam medis di header halaman reservasi)
     */
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
    /**
     * 🔹 Generate No Pemeriksaan unik Format: RSV-YYYYMMDDXXX
     */
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
    // 🔹 ALUR AWAL RESERVASI (PILIH POLI → DOKTER → JADWAL)
    // ============================================================

    //🔹 Langkah 1: Ambil semua daftar Poli
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

    //🔹 Langkah 2: Filter dokter berdasarkan poli & tanggal reservasi
    public function getDokterByPoli(Request $request)
{
    $request->validate([
        'kode_poli' => 'nullable|string'
    ]);

    try {
        // Jika pilih semua → tampilkan semua dokter
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
    //🔹 Langkah 3: Cek jadwal & sisa kuota dokter pada tanggal dipilih
    public function getJadwalDenganKuota(Request $request)
{
    $request->validate([
        'kode_dokter'       => 'nullable|string',
        'kode_poli'         => 'nullable|string',
        'tanggal_reservasi' => 'required|date_format:Y-m-d',
    ]);

    try {
        $tanggalReservasi = $request->tanggal_reservasi;

        $hariInggris = Carbon::parse($tanggalReservasi)->format('l');
        $hariMapping = [
            'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
            'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7,
        ];
        $kodeHari = $hariMapping[$hariInggris];

        // MODE FILTER DOKTER
        if (!empty($request->kode_dokter) && strtolower($request->kode_dokter) !== "semua") {
            $jadwalQuery = MasterJadwal::where('kode_dokter', $request->kode_dokter);
        } else {
            $jadwalQuery = MasterJadwal::query();
        }

        // MODE FILTER POLI
        if (!empty($request->kode_poli) && strtolower($request->kode_poli) !== 'semua') {
            $kodePoli = $request->kode_poli;

            $jadwalQuery->where(function($q) use ($kodePoli) {
                $q->where('kode_poli', $kodePoli)
                  ->orWhereHas('dokter', function ($dq) use ($kodePoli) {
                      $dq->where('kode_poli', $kodePoli);
                  });
            });
        }

        $jadwalDokter = $jadwalQuery->where('hari', $kodeHari)->get();

        if ($jadwalDokter->isEmpty()) {
            return $this->errorResponse('Tidak ada jadwal pada hari ini.', null, 404);
        }

        $hasil = [];

        foreach ($jadwalDokter as $jadwal) {
            $reservasiTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('tanggal_pesan', $tanggalReservasi)
                ->whereIn('status_pembayaran', [
                    'menunggu_pembayaran',
                    'menunggu verifikasi',
                    'terverifikasi'
                ])
                ->count();

            $sisaKuota = $jadwal->quota - $reservasiTerpakai;

            if ($sisaKuota > 0) {
                $hasil[] = [
                    'jadwal_id' => $jadwal->id,
                    'kode_dokter' => $jadwal->kode_dokter,
                    'nama_dokter' => optional($jadwal->dokter)->nama,
                    'kode_poli' => $jadwal->kode_poli,
                    'nama_poli' => optional($jadwal->poli)->nama_poli,
                    'jam_mulai' => $jadwal->jam_mulai,
                    'jam_selesai' => $jadwal->jam_selesai,
                    'kuota_total' => $jadwal->quota,
                    'kuota_terpakai' => $reservasiTerpakai,
                    'sisa_kuota' => $sisaKuota,
                    'hari' => Carbon::parse($tanggalReservasi)->translatedFormat('l'),
                ];
            }
        }

        return $this->successResponse('Data jadwal berhasil diambil', $hasil);

    } catch (Exception $e) {
        Log::error('Get Jadwal Error: '.$e->getMessage());
        return $this->errorResponse('Gagal mengambil data jadwal', null, 500);
    }
}

    // ============================================================
    // 🔹 PROSES RESERVASI & PEMBAYARAN
    // ============================================================

    public function createReservasi(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'rekam_medis_id'    => 'required|string|exists:rekam_medis,rekam_medis',
                'dokter_id'         => 'required|string|exists:master_dokter,kode_dokter',
                'jadwal_id'         => 'required|integer|exists:master_jadwal,id',
                'tanggal_pesan'     => 'required|date_format:Y-m-d',
                'keluhan'           => 'nullable|string|max:255',
                'metode_pembayaran' => 'required|string|in:Transfer Bank,QRIS,Tunai',
                'jenis_pasien'      => 'required|string|in:umum,BPJS,asuransi',
            ]);

            $pasien = RekamMedis::where('rekam_medis', $validated['rekam_medis_id'])->first();
            if (!$pasien) return $this->errorResponse('Data pasien tidak ditemukan', null, 404);

            $dokter = MasterDokter::where('kode_dokter', $validated['dokter_id'])->first();
            if (!$dokter) return $this->errorResponse('Data dokter tidak ditemukan', null, 404);
            
            $jadwal = MasterJadwal::find($validated['jadwal_id']);
            if (!$jadwal) return $this->errorResponse('Jadwal tidak ditemukan', null, 404);

            // 🔍 Validasi kuota ulang sebelum simpan
            $kuotaTotal = (int) ($jadwal->quota ?? 0);
            $reservasiTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('tanggal_pesan', $validated['tanggal_pesan'])
                ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'menunggu verifikasi', 'terverifikasi'])
                ->count();

            if ($reservasiTerpakai >= $jadwal->quota) {
                DB::rollBack();
                return $this->errorResponse('Maaf, kuota untuk jadwal ini sudah penuh.', null, 409);
            }

            $noPemeriksaan = $this->generateNoPemeriksaan();

            $reservasi = Reservasi::create([
                'no_pemeriksaan'    => $noPemeriksaan,
                'pasien_id'         => $pasien->rekam_medis,
                'dokter_id'         => $dokter->kode_dokter,
                'jadwal_id'         => $jadwal->id,
                'tanggal_pesan'     => $validated['tanggal_pesan'],
                'waktu_pesan'       => Carbon::now()->format('H:i:s'),
                'jam_mulai'         => $jadwal->jam_mulai,
                'jam_selesai'       => $jadwal->jam_selesai,
                'keluhan'           => $validated['keluhan'] ?? null,
                'biaya_reservasi'   => 25000,
                'status'            => 'Menunggu Konfirmasi',
                'status_reservasi'  => 'menunggu',
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => 'menunggu_pembayaran',
                'pembayaran_total'  => 25000,
                'jenis_pasien'      => $validated['jenis_pasien'],
            ]);

            DB::commit();

            return $this->successResponse('Reservasi berhasil dibuat. Menunggu pembayaran.', [
                    'no_pemeriksaan'    => $reservasi->no_pemeriksaan,
                    'pasien_id'         => $pasien->rekam_medis,
                    'nama_pasien'       => $pasien->nama_lengkap ?? 'N/A',
                    'nama_dokter'       => $dokter->nama,
                    'spesialisasi'      => $dokter->spesialisasi,
                    'tanggal_kunjungan' => $validated['tanggal_pesan'],
                    'jam_layanan'       => $jadwal->jam_mulai . ' - ' . $jadwal->jam_selesai,
                    'metode_bayar'      => $reservasi->metode_pembayaran,
                    'total_bayar'       => $reservasi->pembayaran_total,
                    'info_pembayaran'   => 'Cek info pembayaran sesuai metode: ' . $validated['metode_pembayaran'],
                ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse('Validasi gagal', $e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Reservasi Error', ['user' => Auth::id(), 'error' => $e->getMessage()]);
            return $this->errorResponse('Terjadi kesalahan server', $e->getMessage(), 500); 
        }
    }

    public function updatePembayaran(Request $request, $no_pemeriksaan)
    {
        try {
            $reservasi = Reservasi::where('no_pemeriksaan', $no_pemeriksaan)->first();

            if (!$reservasi) {
                return $this->errorResponse('Reservasi tidak ditemukan', null, 404);
            }

            $reservasi->update([
                'status_pembayaran' => 'terverifikasi',
                'status_reservasi'  => 'menunggu_kunjungan',
                'status'            => 'Aktif',
            ]);

            return $this->successResponse('Status pembayaran berhasil diperbarui menjadi LUNAS', $reservasi);
        } catch (Exception $e) {
            Log::error('Update Pembayaran Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal update pembayaran', $e->getMessage(), 500);
        }
    }

    public function riwayatReservasi($rekam_medis_id)
    {
        $data = Reservasi::where('pasien_id', $rekam_medis_id)
            ->with(['dokter', 'jadwal.poli'])
            ->orderBy('tanggal_pesan', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return $this->successResponse('Belum ada riwayat reservasi', []);
        }

        return $this->successResponse('Riwayat reservasi ditemukan', $data);
    }
}
