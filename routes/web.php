<?php
use App\Http\Controllers\PromoControllerWeb;
use App\Http\Controllers\DokterControllerWeb;
use App\Http\Controllers\JadwalController;
use Illuminate\Support\Facades\Route;

// Route untuk halaman utama (root) langsung ke dashboard
Route::get('/', function () {
    // Mengembalikan view 'dashboard' (pastikan file dashboard.blade.php ada)
    return view('dashboard');
});

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
Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');