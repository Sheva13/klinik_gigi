<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\ReservasiController; // 👈 tambahkan ini

// Route bebas (guest)
Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/dokter', [DokterController::class, 'index']);

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

// ==============================
// 🔹 ROUTE BARU UNTUK RESERVASI
// ==============================

// Langkah 1 — Ambil semua poli
Route::get('/reservasi/poli', [ReservasiController::class, 'getDaftarPoli']);

// Langkah 2 — Filter dokter berdasarkan poli
Route::post('/reservasi/dokter', [ReservasiController::class, 'getDokterByPoli']);

// Langkah 3 — Tampilkan jadwal dokter & sisa kuota
Route::post('/reservasi/jadwal', [ReservasiController::class, 'getJadwalDenganKuota']);

// Langkah 4 — Buat reservasi (setelah konfirmasi)
Route::post('/reservasi/create', [ReservasiController::class, 'createReservasi']);

// Langkah 5 — Update status pembayaran (misal setelah transaksi berhasil)
Route::put('/reservasi/pembayaran/{no_pemeriksaan}', [ReservasiController::class, 'updatePembayaran']);

// Langkah 6 — Lihat riwayat reservasi pasien
Route::get('/reservasi/riwayat/{rekam_medis_id}', [ReservasiController::class, 'riwayatReservasi']);