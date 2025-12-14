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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ========================================================================
// 🟢 PUBLIC ROUTES (BISA DIAKSES TANPA LOGIN)
// ========================================================================

Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route Callback Midtrans 
Route::post('payment/midtrans-callback', [MidtransWebhookController::class, 'handle']);

// ROUTE — Jadwal Praktek
// Informasi Umum (Dokter & Jadwal Umum)
Route::get('/jadwal-praktek', [DokterController::class, 'getJadwalPraktek']);
Route::get('/dokter', [DokterController::class, 'index']);
Route::get('/dokter/{id}', [DokterController::class, 'show']);
Route::get('/promo', [PromoController::class, 'index']);

// OTP (Password Reset & Verifikasi)
Route::post('/auth/request-otp', [AuthController::class, 'requestOtpEmail']);
Route::post('/auth/verify-otp',  [AuthController::class, 'verifyOtpEmail']);

// ========================================================================
// 🔒 PROTECTED ROUTES (WAJIB LOGIN / ADA TOKEN)
// ========================================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- AUTH & USER ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password/request-change', [UbahPasswordController::class, 'requestOtpForPasswordChange']);
    Route::post('/password/verify-change', [UbahPasswordController::class, 'verifyOtpAndChangePassword']);
    
    // --- PROFIL & PASIEN ---
    Route::get('/profil', [ProfilController::class, 'show']);
    Route::post('/profil/update', [ProfilController::class, 'update']);
    Route::post('/password/verify-change', [UbahPasswordController::class, 'verifyOtp']);
    Route::post('/password/reset', [UbahPasswordController::class, 'resetPassword']);
    Route::post('/homecare/calculate', [HomeCareController::class, 'calculateCost']);

    // --- Rute Autentikasi & Pasien ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/pasien', [PasienController::class, 'index']);
    Route::get('/pasien/me', [PasienController::class, 'me']);
    Route::get('/pasien/{userId}', [PasienController::class, 'showPasienById']);

    // --- RIWAYAT UMUM ---
    Route::get('/riwayat', [RiwayatController::class, 'getRiwayat']);

    // ==============================
    // 🦷 ROUTE RESERVASI KLINIK (FLUTTER)
    // ==============================
    // Kita amankan di sini agar hanya user login yang bisa booking
    
    // Helper: Ambil Data User untuk Form Reservasi
    Route::get('/reservasi/user', [ReservasiController::class, 'getUserData']);

    // Langkah 1: Ambil Poli
    Route::get('/reservasi/poli', [ReservasiController::class, 'getDaftarPoli']);

    // Langkah 2: Filter Dokter (POST karena mungkin nanti butuh kirim param banyak)
    Route::post('/reservasi/dokter', [ReservasiController::class, 'getDokterByPoli']);

    // Langkah 3: Cek Jadwal & Kuota (PENTING: Cek Libur juga ada di sini)
    Route::post('/reservasi/jadwal', [ReservasiController::class, 'getJadwalDenganKuota']);

    // Langkah 4: Create Booking (Submit)
    Route::post('/reservasi/create', [ReservasiController::class, 'createReservasi']);

    // Langkah 5: Update Pembayaran (Opsional via API jika User upload bukti)
    Route::put('/reservasi/pembayaran/{no_pemeriksaan}', [ReservasiController::class, 'updatePembayaran']);

    // Langkah 6: Riwayat Reservasi Spesifik
    Route::get('/reservasi/riwayat/{rekam_medis_id}', [ReservasiController::class, 'riwayatReservasi']);


    // ==============================
    // 🚑 ROUTE HOME CARE
    // ==============================
    Route::get('/homecare/jadwal', [HomeCareController::class, 'getMasterJadwal']);
    Route::post('/homecare/calculate', [HomeCareController::class, 'calculateCost']);
    Route::post('/homecare/book', [HomeCareController::class, 'store']); // Alias lama
    Route::post('/homecare/booking', [HomeCareController::class, 'storeBooking']); // Alias baru
    
    Route::post('/homecare/booking/{id}/konfirmasi-bayar', [HomeCareController::class, 'confirmPayment']);
    Route::get('/homecare/booking/{id}/tracking', [HomeCareController::class, 'getTrackingHistory']);
    Route::post('/homecare/update-status', [HomeCareController::class, 'updateStatus']);
    Route::post('/homecare/finish-treatment/{id}', [HomeCareController::class, 'finishTreatment']);
    Route::get('/homecare/invoice/{id}', [HomeCareController::class, 'getInvoice']);
    Route::post('/homecare/pay-settlement/{id}', [HomeCareController::class, 'paySettlement']);
    Route::get('/homecare/booking/{id}/status', [HomeCareController::class, 'checkPaymentStatus']);
    Route::get('/profil', [ProfilController::class, 'show']);
    Route::post('/profil/update', [ProfilController::class, 'update']);

    // --- UPLOAD FOTO DOKTER ---
    Route::post('/dokter/upload-foto/{id}', [DokterController::class, 'uploadFotoProfil']);

});