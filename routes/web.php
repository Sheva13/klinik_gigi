<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoControllerWeb;
use App\Http\Controllers\DokterControllerWeb;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\HomeCareWebController;
use App\Http\Controllers\AdminReservasiController;  
use App\Http\Controllers\AdminAntrianReservasiController; 
use App\Http\Controllers\AdminPembayaranReservasiController; 

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('auth.login'); 
})->name('home');

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
        Route::get('/', [AdminReservasiController::class, 'index'])->name('reservasi.admin.index');
        Route::get('/create', [AdminReservasiController::class, 'create'])->name('reservasi.admin.create');
        Route::post('/', [AdminReservasiController::class, 'createManual'])->name('reservasi.admin.createManual');
        Route::get('/cari-pasien', [AdminReservasiController::class, 'cariPasien'])->name('reservasi.admin.cariPasien');
        Route::get('/antrian', [AdminAntrianReservasiController::class, 'antrianIndex'])->name('reservasi.admin.antrian');
        Route::get('/{id}/edit', [AdminReservasiController::class, 'edit'])->name('reservasi.admin.edit');
        Route::put('/{id}', [AdminReservasiController::class, 'update'])->name('reservasi.admin.update'); 
        Route::get('/{id}', [AdminReservasiController::class, 'show'])->name('reservasi.admin.show');
        Route::put('/{id}/status', [AdminReservasiController::class, 'updateStatusReservasi'])->name('reservasi.admin.status');
        Route::get('/{id}/pembayaran', [AdminPembayaranReservasiController::class, 'showPayment'])->name('reservasi.admin.showPayment');
        Route::post('/{id}/tandai-lunas', [AdminPembayaranReservasiController::class, 'tandaiLunas'])->name('reservasi.admin.tandaiLunas');
        Route::put('/{id}/verify-payment', [AdminPembayaranReservasiController::class, 'verifyPayment'])->name('reservasi.admin.updatePembayaran'); 
    });

    // --- ROUTES HOME CARE WEB ADMIN ---
    Route::middleware(['auth'])->group(function () {
        Route::get('/homecare', [HomeCareWebController::class, 'index'])->name('homecare.index');
        Route::get('/homecare/{id}', [HomeCareWebController::class, 'show'])->name('homecare.show');
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