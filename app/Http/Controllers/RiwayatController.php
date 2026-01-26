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
                        'biaya' => 'Rp ' . number_format($item->pembayaran_total ?? 0, 0, ',', '.'),
                        'total_biaya_tindakan' => 'Rp ' . number_format($item->total_biaya_tindakan ?? 0, 0, ',', '.'),

                        // Payment links/tokens
                        'redirect_url' => $item->redirect_url ?? $item->link_pembayaran ?? null,
                        'snap_token' => $item->snap_token ?? null,

                        'nama' => $item->pasien?->nama ?? '-',
                        'rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                        'no_rekam_medis' => $item->pasien?->rekam_medis ?? '-',
                        'foto' => $item->pasien?->file_foto ? asset('storage/' . $item->pasien?->file_foto) : '',

                        'pasien_id' => $item->pasien_id,
                        'dokter_id' => $item->dokter_id,
                        'jadwal_id' => $item->jadwal_id,
                        'no_antrian' => $item->no_antrian ?? null,
                        'status_pembayaran' => $item->status_pembayaran ?? null,
                        'status_pelunasan' => $item->status_pelunasan ?? null,
                        'metode_pembayaran' => $item->metode_pembayaran ?? null,
                        'jenis_pasien' => $item->jenis_pasien ?? null,
                        'pembayaran_total' => 'Rp ' . number_format($item->pembayaran_total ?? 0, 0, ',', '.'),
                        'keluhan' => $item->keluhan ?? null,
                        'created_at' => $item->created_at?->toDateTimeString() ?? null,
                        'updated_at' => $item->updated_at?->toDateTimeString() ?? null,

                        // Payment button availability (available for 1 hour after creation)
                        'payment_available' => $item->created_at ? now()->diffInHours($item->created_at) < 1 : false,

                        'full_reservasi' => $item->toArray(),
                    ];
                });
                
            // Build homecare list
            // NOTE: some records use pasien_id (string, rekam_medis) while others may populate rekam_medis_id (numeric)
            // Be permissive: match either pasien_id == rekam_medis OR rekam_medis_id == id
            $homecareQuery = HomeCareReservasi::with([
                'pasien',
                'dokter',
                'jadwalHarian',
            ])
                ->where(function ($q) use ($rekamMedis) {
                    $q->where('pasien_id', $rekamMedis->rekam_medis)
                      ->orWhere('rekam_medis_id', $rekamMedis->id);
                });

            Log::info('RiwayatController - HomeCare query built', [
                'rekam_medis' => $rekamMedis->rekam_medis,
                'rekam_medis_id' => $rekamMedis->id,
                'sql' => $homecareQuery->toSql(),
            ]);

            $homecareCount = $homecareQuery->count();
            Log::info('RiwayatController - HomeCare count', ['count' => $homecareCount]);

            $riwayatHomeCare = $homecareQuery
                ->orderBy('tanggal_pesan', 'desc')
                ->get()
                ->map(function ($item) {
                    // Local fallbacks: try pasien relation -> rekamMedis relation -> direct pasien_id lookup
                    $pasienNama = $item->pasien?->nama ?? $item->rekamMedis?->nama ?? RekamMedis::where('rekam_medis', $item->pasien_id)->value('nama');
                    $pasienRm = $item->pasien?->rekam_medis ?? $item->rekamMedis?->rekam_medis ?? $item->pasien_id;

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
                        'status_pelunasan' => $item->status_pelunasan ?? null,
                        'status' => $item->status ?? null,
                        // Normalize status_pembayaran: prefer status_booking, fallback to status_pelunasan or status
                        'status_pembayaran' => $item->status_booking ?? $item->status_pelunasan ?? $item->status ?? null,

                        // Payment links/tokens
                        'redirect_url' => $item->redirect_url ?? $item->link_pembayaran ?? null,
                        'snap_token' => $item->snap_token ?? null,

                        // Nama & Rekam Medis: gunakan beberapa fallback sehingga detail HomeCare menampilkan data
                        'nama' => $pasienNama ?? ($item->pasien_id ?? '-'),
                        'rekam_medis' => $pasienRm ?? ($item->pasien_id ?? '-'),
                        'no_rekam_medis' => $pasienRm ?? ($item->pasien_id ?? '-'),
                        'foto' => $item->pasien?->file_foto ? asset('storage/' . $item->pasien?->file_foto) : '',
                        'keluhan' => $item->keluhan,
                        'alamat_lengkap' => $item->alamat_lengkap,
                        'latitude' => $item->latitude,
                        'longitude' => $item->longitude,

                        // Payment button availability (available for 1 hour after creation)
                        'payment_available' => $item->created_at ? now()->diffInHours($item->created_at) < 1 : false,

                        'created_at' => $item->created_at?->toDateTimeString() ?? null,
                        'updated_at' => $item->updated_at?->toDateTimeString() ?? null,

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
