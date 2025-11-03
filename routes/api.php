<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;

// Route bebas (guest)
Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ Route untuk mengambil SEMUA pasien (sesuai method getPasien)
Route::get('/pasien', [PasienController::class, 'getPasien']);

// ✅ Route BARU untuk mengambil SATU pasien berdasarkan ID (sesuai kebutuhan Flutter)
// PENTING: Ini menggunakan format RESTful dengan parameter {userId} di URL
Route::get('/pasien/{userId}', [PasienController::class, 'showPasienById']);


// 🔒 Route yang butuh token (Auth: Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // ✅ Route ini dipindahkan ke dalam group middleware agar terproteksi
    Route::get('/pasien/me', [PasienController::class, 'me']); 
    
    // Tambahkan route lain yang harus login di sini
});
