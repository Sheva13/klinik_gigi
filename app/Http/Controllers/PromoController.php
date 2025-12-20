<?php

namespace App\Http\Controllers;

use App\Models\MasterPromo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PromoController extends Controller
{
    /**
     * Mengambil daftar promo yang masih aktif.
     */
    public function index(Request $request)
    {
        try {
            $today = Carbon::now('Asia/Jakarta');

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

            $data = $promos->map(function ($promo) use ($baseUrl) {
                
                $fotoUrl = null;
                if (!empty($promo->gambar_banner)) {
                    $path = trim($promo->gambar_banner);
                    
                    // Logika yang sama dengan DokterController untuk membuat URL lengkap
                    if (!str_starts_with($path, 'uploads/')) {
                         $path = 'uploads/' . $path;
                    }
                    $fotoUrl = $baseUrl . '/' . $path;
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
                ];
            });

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