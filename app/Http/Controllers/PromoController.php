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
    public function index()
    {
        try {
            $today = Carbon::today();

            // Ambil promo yang tanggal selesainya >= hari ini
            $promos = MasterPromo::where('tanggal_selesai', '>=', $today)
                                ->orWhereNull('tanggal_selesai') // Atau yang tidak ada tanggal selesainya
                                ->get();

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