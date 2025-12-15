<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use App\Models\HomeCareReservasi;
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
            
            // Build poliklinik list
            $riwayatPoliklinik = Reservasi::with(['pasien', 'dokter', 'jadwal.poli'])
                ->where('pasien_id', $rekamMedis->rekam_medis)
                ->orderBy('tanggal_pesan', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'jenis_layanan' => 'poliklinik',

                        'no_pemeriksaan' => $item->no_pemeriksaan,
                        'dokter' => $item->dokter?->nama ?? '-',
                        'tanggal' => $item->tanggal_pesan,
                        'tanggal_pesan' => $item->tanggal_pesan,
                        'poli' => $item->jadwal?->poli?->nama_poli ?? '-',

                        'status_reservasi' => $item->status_reservasi,
                        'jam_mulai' => $item->jam_mulai ?? '-',
                        'jam_selesai' => $item->jam_selesai ?? '-',
                        'biaya' => $item->pembayaran_total ?? '0',

                        'nama' => $item->pasien?->nama ?? '-',
                        'rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                        'no_rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                        'foto' => $item->pasien?->file_foto ?? '',

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
                        'full_reservasi' => $item->toArray(),
                    ];
                });

            // Build homecare list
            $riwayatHomeCare = HomeCareReservasi::with([
                'pasien',
                'dokter',
                'jadwalHarian',
            ])
                // pasien_id menyimpan nomor rekam_medis (string), bukan id numeric
                ->where('pasien_id', $rekamMedis->id)
                ->orderBy('tanggal_pesan', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'jenis_layanan' => 'homecare',

                        'no_pemeriksaan' => $item->no_pemeriksaan,
                        'dokter' => $item->dokter?->nama ?? '-',
                        'tanggal' => $item->tanggal_pesan,
                        'tanggal_pesan' => $item->tanggal_pesan,
                        'poli' => 'Home Care',

                        'status_reservasi' => $item->status_reservasi,
                        'jam_mulai' => $item->jam_mulai ?? '-',
                        'jam_selesai' => $item->jam_selesai ?? '-',
                        'biaya' => $item->pembayaran_total ?? '0',

                        // Biaya & Pembayaran (support fields expected by frontend)
                        'biaya_reservasi' => $item->biaya_reservasi ?? 0,
                        'biaya_transport' => $item->biaya_transport ?? 0,
                        'pembayaran_total' => $item->pembayaran_total ?? 0,
                        'metode_pembayaran' => $item->metode_pembayaran ?? null,
                        'status_booking' => $item->status_booking ?? null,
                        'status' => $item->status ?? null,
                        'status_pembayaran' => $item->status_pembayaran ?? null,

                        'nama' => $item->pasien?->nama ?? '-',
                        'rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                        'no_rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                        'foto' => $item->pasien?->file_foto ?? '',

                        'no_antrian' => $item->no_antrian,
                        'status_pembayaran' => $item->status_pembayaran,
                        'keluhan' => $item->keluhan,
                        'alamat_lengkap' => $item->alamat_lengkap,
                        'latitude' => $item->latitude,
                        'longitude' => $item->longitude,

                        'full_reservasi' => $item->toArray(),
                    ];
                });

            // Gabungkan dan sortir berdasarkan tanggal_pesan
            $riwayatGabungan = $riwayatPoliklinik
                ->merge($riwayatHomeCare)
                ->sortByDesc('tanggal_pesan')
                ->values();

            // Filter berdasarkan query param 'jenis' jika diberikan
            $jenis = $request->query('jenis');
            if ($jenis === 'poliklinik') {
                $riwayatGabungan = $riwayatGabungan->filter(function ($item) {
                    return $item['jenis_layanan'] === 'poliklinik';
                })->values();
            } elseif ($jenis === 'home' || $jenis === 'homecare') {
                $riwayatGabungan = $riwayatGabungan->filter(function ($item) {
                    return $item['jenis_layanan'] === 'homecare';
                })->values();
            }

            return response()->json([
                'success' => true,
                'data' => $riwayatGabungan
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
