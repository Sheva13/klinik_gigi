<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Anda menambahkan ini, bisa dipakai untuk 'auth()->user()'
use App\Models\MpUser; // Asumsi model user Anda bernama MpUser
use Illuminate\Support\Facades\Log; // Import untuk logging

class PasienController extends Controller
{
    // ✅ 1. Method untuk mengambil data pasien yang sedang login
    // Ini yang akan dipanggil oleh Flutter di Halaman Home/Dashboard
    public function index(Request $request)
    {
        // Ambil data user yang sedang login (yang token-nya dikirim oleh Flutter)
        // Ini adalah model 'User' dari tabel 'users'
        $user = $request->user();

        // dapatkan 'rekam_medis_id' dari user yang login
        $rekamMedisId = $user->rekam_medis_id;

        // Cari data pasien (MpUser) berdasarkan rekam_medis_id milik user yang login
        // Ini adalah model 'MpUser' dari tabel 'rekam_medis'
        $pasien = MpUser::where('rekam_medis_id', $rekamMedisId)->first();

        // Cek jika data pasien tidak ditemukan (untuk keamanan)
        if (!$pasien) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pasien tidak ditemukan. Pastikan user ini terhubung dengan rekam medis.',
            ], 404);
        }

        // Kembalikan data pasien yang benar
        return response()->json([
            'status' => 'success',
            'message' => 'Data pasien berhasil diambil',
            'data' => $pasien,
        ]);
    }

    // ✅ 2. Method untuk mengambil SEMUA data pasien
    // HATI-HATI: Jangan ekspos ini ke user biasa, hanya untuk admin.
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

    // ✅ 3. Method untuk mengambil data user login (model User)
    // Ini mengembalikan data dari tabel 'users' (email, nama, dll)
    public function me(Request $request)
    {
        // PENTING: Route ini HARUS di dalam middleware 'auth:sanctum'
        $user = $request->user(); // atau auth()->user()
        
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User tidak terautentikasi'], 401);
        }

        // Asumsi data user yang login sudah sesuai dengan data pasien yang dibutuhkan
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }
}