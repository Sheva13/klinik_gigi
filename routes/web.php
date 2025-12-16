<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoControllerWeb;
use App\Http\Controllers\DokterControllerWeb;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminReservasiController;
use App\Http\Controllers\HomeCareWebController; // [PENTING] Jangan lupa import ini

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('auth.login'); })->name('home');
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('auth.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login');

/*
|--------------------------------------------------------------------------
| 2. ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:admin')->group(function () {

    // --- DASHBOARD ---
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // --- RESERVASI & ANTRIAN ---
    Route::prefix('admin/reservasi')->group(function () {
        
        // 1. DAFTAR RESERVASI (INDEX)
        Route::get('/', [AdminReservasiController::class, 'index'])->name('reservasi.admin.index');
        
        // 2. CREATE MANUAL
        Route::get('/create', [AdminReservasiController::class, 'create'])->name('reservasi.admin.create');
        Route::post('/', [AdminReservasiController::class, 'createManual'])->name('reservasi.admin.createManual'); // KOREKSI: Gunakan createManual
        
        // 3. CARI PASIEN (AJAX)
        Route::get('/cari-pasien', [AdminReservasiController::class, 'cariPasien'])->name('reservasi.admin.cariPasien');

        // 4. ANTRIAN (Patient Queue)
        Route::get('/antrian', [AdminReservasiController::class, 'antrianIndex'])->name('reservasi.admin.antrian');

        // 5. DETAIL, EDIT, UPDATE DATA RESERVASI
        Route::get('/{id}/edit', [AdminReservasiController::class, 'edit'])->name('reservasi.admin.edit');
        Route::put('/{id}', [AdminReservasiController::class, 'update'])->name('reservasi.admin.update'); // Method PUT
        Route::get('/{id}', [AdminReservasiController::class, 'show'])->name('reservasi.admin.show');
        
        // 6. UPDATE STATUS OPERASIONAL (Menunggu -> Diproses -> Selesai)
        // 🔥 FIX UTAMA: Ubah method dari POST ke PUT, dan gunakan nama route 'reservasi.admin.status'
        Route::put('/{id}/status', [AdminReservasiController::class, 'updateStatusReservasi'])->name('reservasi.admin.status');
        
        // 7. KELOLA PEMBAYARAN MANUAL
        // Route untuk menampilkan form pembayaran/cek bukti
        Route::get('/{id}/pembayaran', [AdminReservasiController::class, 'showPayment'])->name('reservasi.admin.showPayment');
        
        // Route untuk menandai LUNAS (Upload bukti & panggil Queue Logic)
        Route::post('/{id}/tandai-lunas', [AdminReservasiController::class, 'tandaiLunas'])->name('reservasi.admin.tandaiLunas');
        
        // Route untuk update pembayaran dari modal/form detail (PUT karena update status)
        Route::put('/{id}/verify-payment', [AdminReservasiController::class, 'updatePembayaran'])->name('reservasi.admin.updatePembayaran'); 
        
    });

    // Routes Lainnya (Promo, Dokter, Jadwal) - Tetap Sama
    Route::get('/promo', [PromoControllerWeb::class, 'index'])->name('promo.index');
    Route::get('/promo/create', [PromoControllerWeb::class, 'create'])->name('promo.create');
    Route::post('/promo', [PromoControllerWeb::class, 'store'])->name('promo.store');
    Route::get('/promo/{id}/edit', [PromoControllerWeb::class, 'edit'])->name('promo.edit');
    Route::put('/promo/{id}', [PromoControllerWeb::class, 'update'])->name('promo.update');
    Route::delete('/promo/{id}', [PromoControllerWeb::class, 'destroy'])->name('promo.destroy');
    // --- ROUTES HOME CARE WEB ADMIN ---
Route::middleware(['auth'])->group(function () {
    // Halaman List
    Route::get('/homecare', [HomeCareWebController::class, 'index'])->name('homecare.index');
    
    // Halaman Detail
    Route::get('/homecare/{id}', [HomeCareWebController::class, 'show'])->name('homecare.show');
    
    // Action Update Status (Ini yang menyebabkan error sebelumnya)
    Route::post('/homecare/{id}/status', [HomeCareWebController::class, 'updateStatus'])->name('homecare.updateStatus');
});

    // --- PROMO ---
    Route::prefix('promo')->group(function () {
        Route::get('/', [PromoControllerWeb::class, 'index'])->name('promo.index'); 
        Route::get('/create', [PromoControllerWeb::class, 'create'])->name('promo.create');
        Route::post('/', [PromoControllerWeb::class, 'store'])->name('promo.store');
        Route::get('/{id}/edit', [PromoControllerWeb::class, 'edit'])->name('promo.edit');
        Route::put('/{id}', [PromoControllerWeb::class, 'update'])->name('promo.update');
        Route::delete('/{id}', [PromoControllerWeb::class, 'destroy'])->name('promo.destroy');
    });

    // --- DOKTER ---
    Route::resource('dokter', DokterControllerWeb::class);

    // --- JADWAL ---
    Route::prefix('jadwal')->group(function () {
        Route::get('/', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::post('/', [JadwalController::class, 'store'])->name('jadwal.store');
        Route::get('/{id}/edit', [JadwalController::class, 'edit'])->name('jadwal.edit');
        Route::put('/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    });

});