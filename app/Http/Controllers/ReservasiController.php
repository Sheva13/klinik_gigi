<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;
use App\Models\MasterPoli;
use Carbon\Carbon;
use Exception;

class ReservasiController extends Controller
{
    /**
     * 🔹 Generate No Pemeriksaan unik
     * Format: RSV-YYYYMMDDXXX
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

    /**
     * 🔹 Langkah 1: Ambil semua daftar Poli
     */
    public function getDaftarPoli()
    {
        try {
            $poli = MasterPoli::select('kode_poli', 'nama_poli')->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar poli berhasil diambil',
                'data'    => $poli
            ], 200);
        } catch (Exception $e) {
            Log::error('Get Daftar Poli Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data poli'
            ], 500);
        }
    }

    /**
     * 🔹 Langkah 2: Ambil daftar Dokter berdasarkan Poli
     */
    public function getDokterByPoli(Request $request)
    {
        $request->validate(['kode_poli' => 'required|string|exists:master_poli,kode_poli']);
        $kodePoli = $request->kode_poli;

        try {
            $dokterCodes = MasterJadwal::where('kode_poli', $kodePoli)
                ->distinct('kode_dokter')
                ->pluck('kode_dokter');

            $dokter = MasterDokter::whereIn('kode_dokter', $dokterCodes)
                ->select('kode_dokter', 'nama', 'gelar', 'spesialisasi')
                ->get();

            if ($dokter->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada dokter yang tersedia untuk poli ini'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Daftar dokter berhasil difilter',
                'data'    => $dokter
            ], 200);
        } catch (Exception $e) {
            Log::error('Filter Dokter Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memfilter data dokter'
            ], 500);
        }
    }

    /**
     * 🔹 Langkah 3: Cek jadwal & sisa kuota dokter pada tanggal dipilih
     */
    public function getJadwalDenganKuota(Request $request)
    {
        $request->validate([
            'kode_dokter'       => 'required|varchar|exists:master_dokter,kode_dokter',
            'tanggal_reservasi' => 'required|date_format:Y-m-d',
        ]);

        $kodeDokter       = $request->kode_dokter;
        $tanggalReservasi = $request->tanggal_reservasi;
        $namaHari         = Carbon::parse($tanggalReservasi)->format('l');

        $jadwalDokter = MasterJadwal::where('kode_dokter', $kodeDokter)
            ->where('hari', $namaHari)
            ->get();

        if ($jadwalDokter->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak praktik pada tanggal tersebut.',
                'data'    => []
            ], 200);
        }

        $hasilJadwal = [];

        foreach ($jadwalDokter as $jadwal) {
            $reservasiTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('tanggal_pesan', $tanggalReservasi)
                ->whereIn('status_pembayaran', ['Lunas', 'Pending', 'Belum Dibayar'])
                ->count();

            $sisaKuota = $jadwal->quota - $reservasiTerpakai;

            if ($sisaKuota > 0) {
                $hasilJadwal[] = [
                    'jadwal_id'      => $jadwal->id,
                    'kode_poli'      => $jadwal->kode_poli,
                    'jam_mulai'      => $jadwal->jam_mulai,
                    'jam_selesai'    => $jadwal->jam_selesai,
                    'kuota_total'    => $jadwal->quota,
                    'kuota_terpakai' => $reservasiTerpakai,
                    'sisa_kuota'     => $sisaKuota,
                    'nama_poli'      => optional($jadwal->poli)->nama_poli ?? 'N/A',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar jadwal tersedia berhasil diambil.',
            'data'    => $hasilJadwal
        ], 200);
    }

    // ============================================================
    // 🔹 PROSES RESERVASI & PEMBAYARAN
    // ============================================================

    /**
     * 🔹 Buat reservasi baru (setelah konfirmasi & klik bayar)
     */
    public function createReservasi(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'rekam_medis_id'    => 'required|varchar|exists:rekam_medis,rekam_medis',
                'dokter_id'         => 'required|varchar|exists:master_dokter,kode_dokter',
                'id'                => 'required|integer|exists:master_jadwal,id',
                'tanggal_pesan'     => 'required|date_format:Y-m-d',
                'keluhan'           => 'nullable|string|max:255',
                'metode_pembayaran' => 'required|string|in:Transfer Bank,QRIS,Tunai',
                'jenis_pasien'      => 'required|string|in:Lama,Baru',
            ]);

            // 🔍 Cek data pasien
            $pasien = RekamMedis::where('rekam_medis', $validated['rekam_medis_id'])->first();
            if (!$pasien) {
                return response()->json(['success' => false, 'message' => 'Data pasien tidak ditemukan'], 404);
            }

            // 🔍 Cek data dokter
            $dokter = MasterDokter::where('kode_dokter', $validated['dokter_id'])->first();
            if (!$dokter) {
                return response()->json(['success' => false, 'message' => 'Data dokter tidak ditemukan'], 404);
            }

            // 🔍 Cek jadwal
            $jadwal = MasterJadwal::find($validated['jadwal_id']);
            if (!$jadwal) {
                return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan'], 404);
            }

            // 🔍 Validasi kuota ulang sebelum simpan
            $reservasiTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                ->where('tanggal_pesan', $validated['tanggal_pesan'])
                ->whereIn('status_pembayaran', ['Lunas', 'Pending', 'Belum Dibayar'])
                ->count();

            if ($reservasiTerpakai >= $jadwal->quota) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, kuota untuk jadwal ini sudah penuh.'
                ], 409);
            }

            // 🔹 Generate nomor pemeriksaan unik
            $noPemeriksaan = $this->generateNoPemeriksaan();

            // 🔹 Simpan ke tabel reservasi
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
                'status_reservasi'  => 'Pending',
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => 'Belum Dibayar',
                'pembayaran_total'  => 25000,
                'jenis_pasien'      => $validated['jenis_pasien'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservasi berhasil dibuat. Menunggu pembayaran.',
                'data'    => [
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
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Reservasi Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Update status pembayaran (mis. dari webhook gateway)
     */
    public function updatePembayaran(Request $request, $no_pemeriksaan)
    {
        try {
            $reservasi = Reservasi::where('no_pemeriksaan', $no_pemeriksaan)->first();

            if (!$reservasi) {
                return response()->json(['success' => false, 'message' => 'Reservasi tidak ditemukan'], 404);
            }

            $reservasi->update([
                'status_pembayaran' => 'Lunas',
                'status_reservasi'  => 'Berhasil',
                'status'            => 'Aktif',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diperbarui menjadi LUNAS',
                'data'    => $reservasi,
            ]);
        } catch (Exception $e) {
            Log::error('Update Pembayaran Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update pembayaran',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Lihat riwayat reservasi pasien
     */
    public function riwayatReservasi($rekam_medis_id)
    {
        $data = Reservasi::where('pasien_id', $rekam_medis_id)
            ->with(['dokter', 'jadwal.poli'])
            ->orderBy('tanggal_pesan', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada riwayat reservasi',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data'    => $data
        ], 200);
    }
}
