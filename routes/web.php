<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoControllerWeb;
use App\Http\Controllers\DokterControllerWeb;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminReservasiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Bisa Diakses Siapa Saja)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('auth.login');
})->name('home');

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

    // --- MANAJEMEN RESERVASI ---
    Route::prefix('admin/reservasi')->group(function () {
        // 1. Index (Tabel Utama)
        Route::get('/', [AdminReservasiController::class, 'index'])->name('reservasi.admin.index');
        
        // 2. Create Manual (Form & Store)
        Route::get('/create', [AdminReservasiController::class, 'create'])->name('reservasi.admin.create');
        Route::post('/', [AdminReservasiController::class, 'createManual'])->name('reservasi.admin.store');

        // 💡 AJAX Search Pasien Lama
        Route::get('/cari-pasien', [AdminReservasiController::class, 'cariPasien'])->name('reservasi.admin.cariPasien');

        // 3. Edit & Update
        Route::get('/{id}/edit', [AdminReservasiController::class, 'edit'])->name('reservasi.admin.edit');
        Route::put('/{id}', [AdminReservasiController::class, 'update'])->name('reservasi.admin.update');
        
        // 4. Pembayaran (Page Khusus)
        Route::get('/{id}/pembayaran', [AdminReservasiController::class, 'showPayment'])->name('admin.reservasi.pembayaran');
        Route::post('/{id}/tandai-lunas', [AdminReservasiController::class, 'tandaiLunas'])->name('reservasi.admin.tandaiLunas');

        // 5. Show Detail & Status Actions
        Route::get('/{id}', [AdminReservasiController::class, 'show'])->name('reservasi.admin.show');
        Route::post('/{id}/status', [AdminReservasiController::class, 'updateStatusReservasi'])->name('reservasi.admin.status');
        Route::post('/{id}/verify-payment', [AdminReservasiController::class, 'updatePembayaran'])->name('reservasi.admin.verifyPayment');
    });

    // --- MANAJEMEN LAINNYA (Promo, Dokter, Jadwal) ---
    Route::get('/promo', [PromoControllerWeb::class, 'index'])->name('promo.index'); 
    Route::get('/promo/create', [PromoControllerWeb::class, 'create'])->name('promo.create');
    Route::post('/promo', [PromoControllerWeb::class, 'store'])->name('promo.store');
    Route::get('/promo/{id}/edit', [PromoControllerWeb::class, 'edit'])->name('promo.edit');
    Route::put('/promo/{id}', [PromoControllerWeb::class, 'update'])->name('promo.update');
    Route::delete('/promo/{id}', [PromoControllerWeb::class, 'destroy'])->name('promo.destroy');

    Route::resource('dokter', DokterControllerWeb::class);

    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
    Route::get('/jadwal/{id}/edit', [JadwalController::class, 'edit'])->name('jadwal.edit');
    Route::put('/jadwal/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
});