<?php
use App\Http\Controllers\PromoControllerWeb;
use App\Http\Controllers\AdminAuthController;
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

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('auth.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login');

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});
