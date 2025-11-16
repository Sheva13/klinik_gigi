<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\HomeCareController;
use App\Http\Controllers\ReservasiController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ProfilController;


// Route Publik (Bebas / Guest)
Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/dokter', [DokterController::class, 'index']);
Route::get('/pasien', [PasienController::class, 'getPasien']);
Route::get('/pasien/{userId}', [PasienController::class, 'showPasienById']);
Route::get('/riwayat', [RiwayatController::class, 'getRiwayat']);
Route::get('/promo', [PromoController::class, 'index']);
Route::post('/auth/request-otp', [AuthController::class, 'requestOtpEmail']);
Route::post('/auth/verify-otp',  [AuthController::class, 'verifyOtpEmail']);



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
    
    Route::get('/profil', [ProfilController::class, 'show']);
    Route::post('/profil/update', [ProfilController::class, 'update']);
});
    Route::get('/pasien/me', [PasienController::class, 'me']);

// ==============================
// 🔹 ROUTE UNTUK RESERVASI
// ==============================

// Langkah 1 — Ambil semua poli
Route::get('/reservasi/poli', [ReservasiController::class, 'getDaftarPoli']);

// Langkah 2 — Filter dokter berdasarkan poli
Route::post('/reservasi/dokter', [ReservasiController::class, 'getDokterByPoli']);

// Langkah 3 — Tampilkan jadwal dokter & sisa kuota
// Parameter: kode_dokter, tanggal_reservasi
Route::post('/reservasi/jadwal', [ReservasiController::class, 'getJadwalDenganKuota']);

// Langkah 4 — Buat reservasi (setelah konfirmasi & pilih metode pembayaran)
Route::post('/reservasi/create', [ReservasiController::class, 'createReservasi']);

// Langkah 5 — Update status pembayaran (misal setelah transaksi berhasil)
Route::put('/reservasi/pembayaran/{no_pemeriksaan}', [ReservasiController::class, 'updatePembayaran']);

// Langkah 6 — Lihat riwayat reservasi pasien
Route::get('/reservasi/riwayat/{rekam_medis_id}', [ReservasiController::class, 'riwayatReservasi']);
