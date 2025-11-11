<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\HomeCareController;

// Route Publik (Bebas / Guest)
Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Mengambil daftar semua dokter (publik)
Route::get('/dokter', [DokterController::class, 'index']);


// Route Terproteksi (Wajib Login / Kirim Token)
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Rute Autentikasi & Pasien ---
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // (Dashboard Flutter) Mengambil data pasien (MpUser) berdasarkan token
    Route::get('/pasien', [PasienController::class, 'index']);

    // Mengambil data user (tabel users) berdasarkan token
    Route::get('/pasien/me', [PasienController::class, 'me']); 
    
    // Jika butuh rute untuk admin mengambil SEMUA pasien:
    // Route::get('/pasien/all', [PasienController::class, 'getPasien']);

    
    // --- RUTE BARU UNTUK DENTAL HOME CARE ---

    /**
     * Mendapatkan daftar jadwal dokter untuk Home Care
     * Method: GET
     * Endpoint: /api/homecare/jadwal
     */
    Route::get('/homecare/jadwal', [HomeCareController::class, 'getMasterJadwal']);

    /**
     * Pasien membuat booking Home Care baru
     * Method: POST
     * Endpoint: /api/homecare/booking
     * Body: { master_jadwal_id, tanggal, keluhan, latitude_pasien, longitude_pasien }
     */
    Route::post('/homecare/booking', [HomeCareController::class, 'storeBooking']);

    /**
     * Pasien konfirmasi sudah bayar DP
     * Method: POST
     * Endpoint: /api/homecare/booking/{id}/konfirmasi-bayar
     */
    Route::post('/homecare/booking/{id}/konfirmasi-bayar', [HomeCareController::class, 'confirmPayment']);

    /**
     * Pasien melihat riwayat tracking per booking
     * Method: GET
     * Endpoint: /api/homecare/booking/{id}/tracking
     */
    Route::get('/homecare/booking/{id}/tracking', [HomeCareController::class, 'getTrackingHistory']);

});