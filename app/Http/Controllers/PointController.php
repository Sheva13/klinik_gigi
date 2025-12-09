<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Digunakan untuk mendapatkan pengguna yang login
use App\Models\UserPoint;
use App\Models\PointHistory;

class PointController extends Controller
{
    /**
     * Mengambil saldo poin pengguna yang sedang login.
     * Endpoint: GET /api/points/current
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentPoints(Request $request)
    {
        // Mendapatkan pengguna yang sedang login (asumsi menggunakan middleware 'auth:sanctum')
        $user = Auth::user(); 

        if (!$user) {
            // Seharusnya tidak terjadi jika route sudah dilindungi middleware
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        // 1. Cari saldo poin pengguna di tabel user_points
        $userPoint = UserPoint::where('user_id', $user->id)->first();

        // 2. Tentukan poin saat ini. Jika data tidak ada (pengguna baru), poin = 0
        $points = $userPoint ? $userPoint->current_points : 0;

        // 3. Kembalikan data poin dalam format JSON
        return response()->json([
            'status' => 'success',
            'current_points' => $points,
        ]);
    }

    /**
     * Mengambil riwayat transaksi poin pengguna yang sedang login.
     * Endpoint: GET /api/points/history
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPointHistory(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        // 1. Ambil riwayat poin, diurutkan dari yang terbaru (descending)
        $history = PointHistory::where('user_id', $user->id)
            ->select('transaction_type', 'amount', 'description', 'created_at') // Pilih kolom yang relevan
            ->orderByDesc('created_at')
            ->get();
        
        // 2. Kembalikan daftar riwayat dalam format JSON
        return response()->json([
            'status' => 'success',
            // Gunakan method toArray() untuk memastikan outputnya bersih dari object Eloquent
            'history' => $history->toArray(), 
        ]);
    }

    /**
     * Metode untuk penukaran poin (Redeem) - Logika ini bisa dikembangkan nanti.
     * Endpoint: POST /api/rewards/redeem
     */
    public function redeemReward(Request $request)
    {
        // Saat ini, kembalikan respons sukses dummy. 
        // Logic di sini harus mencakup validasi, cek saldo, pengurangan poin, dan pencatatan riwayat.
        return response()->json([
            'status' => 'success',
            'message' => 'Penukaran hadiah berhasil. (LOGIC PENGURANGAN POIN BELUM DIIMPLEMENTASIKAN)',
        ]);
    }
}
