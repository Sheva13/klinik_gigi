<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpUser; // Asumsi model user Anda bernama MpUser
use Illuminate\Support\Facades\Log; // Import untuk logging

class PasienController extends Controller
{
    // ✅ 1. Method untuk mengambil SATU data pasien berdasarkan ID pengguna
    // Endpoint ini akan dipanggil oleh Flutter: /api/pasien/{userId}
    public function showPasienById(string $userId)
    {
        try {
            // PENTING: Menggunakan 'where' dan 'first()' untuk mencari berdasarkan kolom 'user_id'
            $pasien = MpUser::where('user_id', $userId)->first();

            if (!$pasien) {
                // Return 404 jika pasien tidak ditemukan
                Log::info("Pasien not found for user_id: " . $userId);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien dengan ID pengguna ' . $userId . ' tidak ditemukan.'
                ], 404);
            }

            // 💡 PERBAIKAN: Konversi objek Eloquent menjadi array atau JSON string yang bersih
            // Menggunakan toArray() memastikan Eloquent tidak mengirim metadata yang tidak diperlukan
            // dan memberi Laravel array PHP bersih untuk di-encode ke JSON.
            $pasienData = $pasien->toArray();

            return response()->json([
                'status' => 'success',
                'data' => $pasienData // Mengembalikan array bersih
            ]);
        } catch (\Exception $e) {
            // Logging error server internal
            Log::error("PasienController Error (showPasienById): " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat data pasien karena kesalahan server.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ 2. Method untuk mengambil SEMUA data pasien (Endpoint: /api/pasien)
    public function getPasien()
    {
        try {
            $data = MpUser::all();
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error("PasienController Error (getPasien): " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat semua data pasien.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ 3. Method untuk mengambil data pasien yang sedang login (Endpoint: /api/me)
    public function me(Request $request)
    {
        // PENTING: Route ini HARUS di dalam middleware 'auth:sanctum'
        $user = auth()->user();

        if (!$user) {
            // Ini seharusnya tidak terjadi jika middleware berjalan dengan benar, tapi sebagai fallback:
            return response()->json(['status' => 'error', 'message' => 'User tidak terautentikasi'], 401);
        }

        // Asumsi data user yang login sudah sesuai dengan data pasien yang dibutuhkan
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }
}
