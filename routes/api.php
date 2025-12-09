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
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UbahPasswordController;
use App\Http\Controllers\MidtransWebhookController;

// Route Publik (Bebas / Guest)
Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route Callback Midtrans 
Route::post('payment/midtrans-callback', [MidtransWebhookController::class, 'handle']);

// ROUTE — Jadwal Praktek
Route::get('/jadwal-praktek', [DokterController::class, 'getJadwalPraktek']);

// Rute ini (GET) tetap publik agar semua orang bisa melihat daftar dokter
// Endpoint ini sekarang mendukung pencarian: /api/dokter?search=nama
Route::get('/dokter', [DokterController::class, 'index']);
Route::get('/dokter/{id}', [DokterController::class, 'show']);
Route::get('/pasien', [PasienController::class, 'getPasien']);
Route::get('/pasien/{userId}', [PasienController::class, 'showPasienById']);
Route::get('/promo', [PromoController::class, 'index']);
Route::post('/auth/request-otp', [AuthController::class, 'requestOtpEmail']);
Route::post('/auth/verify-otp',  [AuthController::class, 'verifyOtpEmail']);
Route::post('/tes-langsung', function () {
    return response()->json(['message' => 'Route Tes Berhasil!']);
});

// Route Terproteksi (Wajib Login / Kirim Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/password/request-change', [UbahPasswordController::class, 'requestOtpForPasswordChange']);
    Route::post('/password/verify-change', [UbahPasswordController::class, 'verifyOtpAndChangePassword']);
    Route::post('/homecare/calculate', [HomeCareController::class, 'calculateCost']);

    // --- Rute Autentikasi & Pasien ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/pasien', [PasienController::class, 'index']);
    Route::get('/pasien/me', [PasienController::class, 'me']); 
    
    // Jika butuh rute untuk admin mengambil SEMUA pasien:
    // Route::get('/pasien/all', [PasienController::class, 'getPasien']);

    // 🔹 Riwayat reservasi berdasarkan user yang login
    Route::get('/riwayat', [RiwayatController::class, 'getRiwayat']);
    
    // Router untuk RESERVASI //
    Route::get('/reservasi/user', [ReservasiController::class, 'getUserData']);
    
    // --- ROUTE UNTUK DENTAL HOME CARE ---
    Route::get('/homecare/jadwal-master', [HomeCareController::class, 'getMasterJadwal']);
    Route::get('/homecare/jadwal', [HomeCareController::class, 'getMasterJadwal']);
    Route::post('/homecare/create-booking', [HomeCareController::class, 'storeBooking']);
    Route::post('/homecare/booking/{id}/konfirmasi-bayar', [HomeCareController::class, 'confirmPayment']);
    Route::get('/homecare/booking/{id}/tracking', [HomeCareController::class, 'getTrackingHistory']);
    Route::post('/homecare/update-status', [HomeCareController::class, 'updateStatus']);
    Route::post('/homecare/finish-treatment/{id}', [HomeCareController::class, 'finishTreatment']);
    Route::get('/homecare/invoice/{id}', [HomeCareController::class, 'getInvoice']);
    Route::post('/homecare/pay-settlement/{id}', [HomeCareController::class, 'paySettlement']);
    Route::get('/homecare/booking/{id}/status', [HomeCareController::class, 'checkPaymentStatus']);
    Route::get('/profil', [ProfilController::class, 'show']);
    Route::post('/profil/update', [ProfilController::class, 'update']);


    // ==========================================================
    // 🔹 ROUTE UPLOAD FOTO DOKTER
    // ==========================================================
    // Route ini sekarang hanya mewajibkan user untuk LOGIN.
    Route::post('/dokter/upload-foto/{id}', [DokterController::class, 'uploadFotoProfil']);
          // ->middleware('admin'); // nonaktifkan sementara sampai ada role admin/pasien untuk users

});
    
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

