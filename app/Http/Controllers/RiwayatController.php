<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RiwayatController extends Controller
{
    /**
     * Ambil riwayat reservasi berdasarkan user yang login
     * Hanya user yang login dapat melihat riwayat miliknya sendiri
     */
    public function getRiwayat(Request $request)
    {
        try {
            // Dapatkan user yang sedang login
            $user = $request->user();
            
            Log::info('RiwayatController - Request received', [
                'user' => $user ? $user->user_id : 'NULL'
            ]);

            if (!$user) {
                Log::warning('RiwayatController - User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau belum login'
                ], 401);
            }

            // Jika user tidak memiliki rekam_medis_id, kembalikan array kosong
            if (!$user->rekam_medis_id) {
                Log::warning('RiwayatController - User has no rekam_medis_id', [
                    'user_id' => $user->user_id
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'User tidak memiliki rekam medis',
                    'data' => []
                ]);
            }

            // Ambil data rekam medis untuk mendapatkan nomor rekam medis
            $rekamMedis = RekamMedis::find($user->rekam_medis_id);

            if (!$rekamMedis) {
                Log::warning('RiwayatController - Rekam medis not found', [
                    'user_id' => $user->user_id,
                    'rekam_medis_id' => $user->rekam_medis_id
                ]);
                
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            Log::info('RiwayatController - User info', [
                'user_id' => $user->user_id,
                'rekam_medis_id' => $user->rekam_medis_id,
                'rekam_medis_no' => $rekamMedis->rekam_medis,
            ]);

            // PENTING: pasien_id dalam tabel reservasi menyimpan nomor rekam_medis (string)
            // BUKAN ID dari tabel rekam_medis
            // Contoh: pasien_id = "RM002", bukan pasien_id = 1
            
            // Muat relasi jadwal.poli juga agar frontend menerima struktur yang sama
            $riwayat = Reservasi::with(['pasien', 'dokter', 'jadwal.poli'])
                ->where('pasien_id', $rekamMedis->rekam_medis) // Filter dengan nomor rekam medis
                ->orderBy('tanggal_pesan', 'desc')
                ->get();

            Log::info('RiwayatController - Query result', [
                'pasien_id_filter' => $rekamMedis->rekam_medis,
                'count' => $riwayat->count()
            ]);

            // Mapping data
            $mappedRiwayat = $riwayat->map(function ($item) {
                // Pertahankan mapping lama, tambahkan field tambahan agar sejalan
                // dengan response dari ReservasiController (tanpa menghilangkan data lama).
                return [
                    // Informasi reservasi (mapping lama)
                    'no_pemeriksaan' => $item->no_pemeriksaan,
                    'dokter' => $item->dokter?->nama ?? '-',
                    'tanggal' => $item->tanggal_pesan,
                    'tanggal_pesan' => $item->tanggal_pesan,
                    'poli' => $item->jadwal?->poli?->nama_poli ?? '-',
                    'status_reservasi' => $item->status_reservasi,
                    'jam_mulai' => $item->jam_mulai ?? '-',
                    'jam_selesai' => $item->jam_selesai ?? '-',
                    'biaya' => $item->pembayaran_total ?? '0',

                    // Informasi pasien (dari relasi RekamMedis)
                    'nama' => $item->pasien?->nama ?? '-',
                    'rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                    'no_rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                    'foto' => $item->pasien?->file_foto ?? '',

                    // Tambahan: fields yang biasanya ada di Reservasi model
                    'pasien_id' => $item->pasien_id,
                    'dokter_id' => $item->dokter_id,
                    'jadwal_id' => $item->jadwal_id,
                    'no_antrian' => $item->no_antrian ?? null,
                    'status_pembayaran' => $item->status_pembayaran ?? null,
                    'metode_pembayaran' => $item->metode_pembayaran ?? null,
                    'jenis_pasien' => $item->jenis_pasien ?? null,
                    'pembayaran_total' => $item->pembayaran_total ?? null,
                    'keluhan' => $item->keluhan ?? null,
                    'created_at' => $item->created_at?->toDateTimeString() ?? null,
                    'updated_at' => $item->updated_at?->toDateTimeString() ?? null,

                    // Sertakan representasi penuh dari model (termasuk relasi)
                    // sehingga frontend bisa mengakses semua field tanpa mengubah mapping lama.
                    'full_reservasi' => $item->toArray(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $mappedRiwayat
            ]);

        } catch (\Exception $e) {
            Log::error('RiwayatController Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil riwayat',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
