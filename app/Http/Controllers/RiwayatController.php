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
            
            $riwayat = Reservasi::with(['pasien', 'dokter', 'jadwal'])
                ->where('pasien_id', $rekamMedis->rekam_medis) // Filter dengan nomor rekam medis
                ->orderBy('tanggal_pesan', 'desc')
                ->get();

            Log::info('RiwayatController - Query result', [
                'pasien_id_filter' => $rekamMedis->rekam_medis,
                'count' => $riwayat->count()
            ]);

            // Mapping data
            $mappedRiwayat = $riwayat->map(function ($item) {
                return [
                    // Informasi reservasi
                    'no_pemeriksaan' => $item->no_pemeriksaan,
                    'dokter' => $item->dokter?->nama ?? '-',
                    'tanggal' => $item->tanggal_pesan,
                    'tanggal_pesan' => $item->tanggal_pesan,
                    'poli' => $item->jadwal?->poli?->nama_poli ?? '-',
                    'status_reservasi' => $item->status_reservasi,
                    'jam_mulai' => $item->jam_mulai ?? '-',
                    'jam_selesai' => $item->jam_selesai ?? '-',
                    'biaya' => $item->biaya_reservasi ?? '0',
                    
                    // Informasi pasien (dari relasi RekamMedis)
                    'nama' => $item->pasien?->nama ?? '-',
                    'rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                    'no_rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                    'foto' => $item->pasien?->file_foto ?? '',
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
