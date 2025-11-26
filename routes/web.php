<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoControllerWeb;
use App\Http\Controllers\DokterControllerWeb;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminReservasiController;
use Illuminate\Support\Facades\Route;

// Route untuk halaman utama (root) langsung ke dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// PROMO
Route::get('/promo', [PromoControllerWeb::class, 'index'])->name('promo.index');
Route::delete('/promo/{id}', [PromoControllerWeb::class, 'destroy'])->name('promo.destroy');
Route::get('/promo/create', [PromoControllerWeb::class, 'create'])->name('promo.create');
Route::post('/promo', [PromoControllerWeb::class, 'store'])->name('promo.store');
Route::get('/promo/{id}/edit', [PromoControllerWeb::class, 'edit'])->name('promo.edit');
Route::put('/promo/{id}', [PromoControllerWeb::class, 'update'])->name('promo.update');

// DOKTER
Route::resource('dokter', DokterControllerWeb::class);

// JADWAL PRAKTEK
Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
Route::get('/jadwal/{id}/edit', [JadwalController::class, 'edit'])->name('jadwal.edit');
Route::put('/jadwal/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');

// LOGIN ADMIN
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('auth.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login');

// ROUTE YANG BUTUH LOGIN ADMIN
Route::middleware('auth:admin')->group(function () {

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // RESERVASI ADMIN (tetap /admin/reservasi)
   Route::prefix('admin/reservasi')->group(function () {
    Route::get('/', [AdminReservasiController::class, 'index'])->name('reservasi.admin.index');
    Route::get('/create', [AdminReservasiController::class, 'create'])->name('reservasi.admin.create');
    Route::post('/', [AdminReservasiController::class, 'storeManual'])->name('reservasi.admin.store');
    Route::get('/{id}', [AdminReservasiController::class, 'show'])->name('reservasi.admin.show');
    Route::post('/{id}/status', [AdminReservasiController::class, 'updateStatus'])->name('reservasi.admin.status');
    Route::post('/{id}/verify-payment', [AdminReservasiController::class, 'verifyPayment'])->name('reservasi.admin.verifyPayment');

    });

});
