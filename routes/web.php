<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoControllerWeb;
use App\Http\Controllers\DokterControllerWeb;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminReservasiController; // Controller yang menangani Reservasi
use App\Http\Controllers\QueueController; // <-- Tambahkan QueueController
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Bisa Diakses Siapa Saja)
|--------------------------------------------------------------------------
*/

// Halaman Utama: LANGSUNG KE LOGIN ADMIN
Route::get('/', function () {
    return redirect()->route('auth.login');
})->name('home');

// Login Admin
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('auth.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login');

/*
|--------------------------------------------------------------------------
| 2. ADMIN ROUTES (Wajib Login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:admin')->group(function () {

    // --- DASHBOARD ADMIN ---
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // ==========================================================
    // === MANAJEMEN ANTRIAN PASIEN BARU (PATIENT QUEUE) ===
    // ==========================================================
    Route::prefix('admin/antrian')->group(function () {
        // Route untuk menampilkan halaman utama manajemen antrian (patient_queue.blade.php)
        Route::get('/manajemen', [QueueController::class, 'index'])->name('queue.index');
        
        // Route untuk menerima data form dan memproses pembuatan antrian
        Route::post('/buat', [QueueController::class, 'store'])->name('queue.store');
        
        // Route untuk menampilkan halaman sukses setelah antrian dibuat
        Route::get('/sukses/{nomorAntrian}', [QueueController::class, 'success'])->name('queue.success');
    });
    // ==========================================================


    // --- MANAJEMEN RESERVASI ---
    Route::prefix('admin/reservasi')->group(function () {
        Route::get('/', [AdminReservasiController::class, 'index'])->name('reservasi.admin.index');

        // Punya Teman (Create) - Dipakai oleh tombol (+)
        Route::get('/create', [AdminReservasiController::class, 'create'])->name('reservasi.admin.create');
        Route::post('/', [AdminReservasiController::class, 'createManual'])->name('reservasi.admin.store');

        // 💡 [TAMBAHAN BARU LIXA]: Route untuk Pencarian Pasien Lama via AJAX
        Route::get('/cari-pasien', [AdminReservasiController::class, 'cariPasien'])->name('reservasi.admin.cariPasien');

        // --- BAGIAN TAMBAHAN (EDIT & UPDATE) ---
        // Rute ini memiliki URI yang lebih spesifik dan HARUS diletakkan sebelum /{id}
        Route::get('/{id}/edit', [AdminReservasiController::class, 'edit'])->name('reservasi.admin.edit');
        Route::put('/{id}', [AdminReservasiController::class, 'update'])->name('reservasi.admin.update');
        // -------------------------------------------
        
        // ==========================================================
        // === TAMBAHAN BARU: RUTE UNTUK FORM UBAH JADWAL (PAGE 3) ===
        // ==========================================================
        Route::get('/{reservasi}/edit-jadwal', [AdminReservasiController::class, 'editJadwal'])->name('reservasi.admin.edit_jadwal');
        // ==========================================================

        // ============================================
        // === TAMBAHAN UNTUK PAGE 4 (PEMBAYARAN) ===
        // ============================================
        // Rute untuk menampilkan detail pembayaran (Page 4)
        // Rute ini juga memiliki URI spesifik dan HARUS diletakkan sebelum /{id}
        Route::get('/{id}/pembayaran', [AdminReservasiController::class, 'showPayment'])->name('admin.reservasi.pembayaran');

        // Rute untuk proses 'Tandai sebagai Lunas' dan upload bukti bayar
        Route::post('/{id}/tandai-lunas', [AdminReservasiController::class, 'tandaiLunas'])->name('reservasi.admin.tandaiLunas');
        // ============================================

        // Rute POST Status/Verifikasi Pembayaran (Bukan GET, jadi tidak bentrok)
        Route::post('/{id}/status', [AdminReservasiController::class, 'updateStatusReservasi'])->name('reservasi.admin.status');
        Route::post('/{id}/verify-payment', [AdminReservasiController::class, 'updatePembayaran'])->name('reservasi.admin.verifyPayment');
        
        // ==========================================================
        // === RUTE BARU: UNTUK HALAMAN SUKSES SETELAH CREATE ===
        // ==========================================================
        Route::get('/success/{id}', [AdminReservasiController::class, 'success'])->name('reservasi.admin.success');
        // ==========================================================

        // Route SHOW (Tombol Mata)
        // Rute ini adalah Rute Catch-All /{id} dan HARUS diletakkan di akhir
        Route::get('/{id}', [AdminReservasiController::class, 'show'])->name('reservasi.admin.show');
        Route::get('/{id}/create-antrian', [AdminReservasiController::class, 'createAntrian'])->name('reservasi.admin.createAntrian');

    });


    // --- MANAJEMEN PROMO ---
    Route::get('/promo', [PromoControllerWeb::class, 'index'])->name('promo.index');
    Route::get('/promo/create', [PromoControllerWeb::class, 'create'])->name('promo.create');
    Route::post('/promo', [PromoControllerWeb::class, 'store'])->name('promo.store');
    Route::get('/promo/{id}/edit', [PromoControllerWeb::class, 'edit'])->name('promo.edit');
    Route::put('/promo/{id}', [PromoControllerWeb::class, 'update'])->name('promo.update');
    Route::delete('/promo/{id}', [PromoControllerWeb::class, 'destroy'])->name('promo.destroy');


    // --- MANAJEMEN DOKTER ---
    Route::resource('dokter', DokterControllerWeb::class);


    // --- MANAJEMEN JADWAL ---
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
    Route::get('/jadwal/{id}/edit', [JadwalController::class, 'edit'])->name('jadwal.edit');
    Route::put('/jadwal/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');

});