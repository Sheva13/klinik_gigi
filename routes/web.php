<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoControllerWeb;
use App\Http\Controllers\DokterControllerWeb;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;

// Route untuk halaman utama (root) langsung ke dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/promo', [PromoControllerWeb::class, 'index'])->name('promo.index');
Route::delete('/promo/{id}', [PromoControllerWeb::class, 'destroy'])->name('promo.destroy');

// --- TAMBAHKAN RUTE BARU INI ---
Route::get('/promo/create', [PromoControllerWeb::class, 'create'])->name('promo.create');
Route::post('/promo', [PromoControllerWeb::class, 'store'])->name('promo.store');

// --- TAMBAHKAN INI UNTUK EDIT ---
Route::get('/promo/{id}/edit', [PromoControllerWeb::class, 'edit'])->name('promo.edit');
Route::put('/promo/{id}', [PromoControllerWeb::class, 'update'])->name('promo.update');

// --- TAMBAHKAN ROUTE DOKTER DI SINI ---
Route::resource('dokter', DokterControllerWeb::class);

// Route Jadwal Praktek
Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
// --- TAMBAHKAN DUA BARIS INI ---
Route::get('/jadwal/{id}/edit', [JadwalController::class, 'edit'])->name('jadwal.edit');
Route::put('/jadwal/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
// -------------------------------
Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('auth.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login');

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});
