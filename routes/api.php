<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\DokterController;

// Route Publik (Bebas / Guest)
Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Mengambil daftar semua dokter (publik)
Route::get('/dokter', [DokterController::class, 'index']);

// Route::get('/pasien', [PasienController::class, 'getPasien']); 
// Route::get('/pasien/{userId}', [PasienController::class, 'showPasienById']); // FUNGSI TIDAK ADA


// ====================================================================
// 🔒 Route Terproteksi (Wajib Login / Kirim Token)
// ====================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // ✅ INI ADALAH RUTE YANG BENAR UNTUK DASHBOARD FLUTTER
    // Memanggil PasienController->index() yang mengambil data pasien (MpUser)
    // berdasarkan token user yang login.
    Route::get('/pasien', [PasienController::class, 'index']);

    // ✅ Rute ini untuk mengambil data user dari tabel 'users' (jika perlu)
    // Memanggil PasienController->me()
    Route::get('/pasien/me', [PasienController::class, 'me']); 
    // Route::get('/user/me', [PasienController::class, 'me']); 
    
    // Jika butuh rute untuk admin mengambil SEMUA pasien:
    // Route::get('/pasien/all', [PasienController::class, 'getPasien']);
});