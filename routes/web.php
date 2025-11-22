<?php
use App\Http\Controllers\PromoControllerWeb;
use Illuminate\Support\Facades\Route;

// Route untuk halaman utama (root) langsung ke dashboard
Route::get('/', function () {
    // Mengembalikan view 'dashboard' (pastikan file dashboard.blade.php ada)
    return view('dashboard');
});

Route::get('/promo', [PromoControllerWeb::class, 'index'])->name('promo.index');
Route::delete('/promo/{id}', [PromoControllerWeb::class, 'destroy'])->name('promo.destroy');