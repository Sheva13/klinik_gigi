<?php

namespace App\Http\Controllers;

use App\Models\MasterPromo;
use App\Models\HomeCareReservasi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PromoController extends Controller
{
    /**
     * Mengambil daftar promo yang masih aktif.
     * Filter by user limit if user_id is provided.
     */
    public function index(Request $request)
    {
        try {
            $today = Carbon::now('Asia/Jakarta');
            $userId = $request->query('user_id'); // Optional: Flutter sends user_id when logged in

            $query = MasterPromo::query();

            // 1. Filter Tanggal (Gabungan Logic: Sudah mulai DAN (belum berakhir ATAU tidak ada batas akhir))
            $query->whereDate('tanggal_mulai', '<=', $today)
                  ->where(function($q) use ($today) {
                      $q->whereDate('tanggal_selesai', '>=', $today)
                        ->orWhereNull('tanggal_selesai');
                  });

            // 2. Filter Tipe Transaksi (Logic dari HomeCare)
            // Param: type = 'booking' | 'settlement' | 'all'
            $type = $request->query('type'); 
            
            if ($type && $type != 'all') {
                $target = ($type == 'settlement') ? 'pelunasan' : 'booking';
                $query->whereIn('target_transaksi', [$target, 'semua']);
            }

            $query->orderBy('id', 'desc');
            $promos = $query->get();

            // Tentukan base URL untuk gambar
            $baseUrl = asset('');

            $data = $promos->map(function ($promo) use ($baseUrl, $userId) {
                
                $fotoUrl = null;
                if (!empty($promo->gambar_banner)) {
                    // Extract filename (clean from path)
                    $filename = basename($promo->gambar_banner);
                    
                    // Use Proxy Route for robust access (Web & Mobile)
                    // url() helper creates absolute URL: http://host/api/promo-image/filename
                    $fotoUrl = url('/api/promo-image/' . $filename);
                }

                // Calculate remaining uses for this user
                $remainingUses = null;
                $usageCount = 0;
                $isAvailable = true;
                
                if ($promo->limit_per_user && $userId) {
                    $usageCount = HomeCareReservasi::where('pasien_id', $userId)
                        ->where('promo_id', $promo->id)
                        ->count();
                    $remainingUses = max(0, $promo->limit_per_user - $usageCount);
                    $isAvailable = $remainingUses > 0;
                }

                return [
                    'id' => $promo->id,
                    'judul_promo' => $promo->judul_promo,
                    'deskripsi' => $promo->deskripsi,
                    'kode_promo' => $promo->kode_promo ?? null,
                    'tipe' => $promo->tipe, 
                    'nilai_potongan' => $promo->nilai_potongan,
                    'harga_poin' => $promo->harga_poin,
                    'target_transaksi' => $promo->target_transaksi,
                    'gambar_banner' => $fotoUrl, // Kirim URL lengkap
                    'tanggal_mulai' => $promo->tanggal_mulai,
                    'tanggal_selesai' => $promo->tanggal_selesai,
                    'limit_per_user' => $promo->limit_per_user,
                    'usage_count' => $usageCount,
                    'remaining_uses' => $remainingUses,
                    'is_available' => $isAvailable, // false if limit reached
                ];
            });

            // Filter out unavailable promos if user_id is provided
            if ($userId) {
                $data = $data->filter(function($promo) {
                    return $promo['is_available'];
                })->values();
            }

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data promo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
