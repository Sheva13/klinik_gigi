<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Halaman Utama (Wajib GET agar bisa dibuka di browser)
Route::get('/', function () {
    return view('auth/login'); // Pastikan file resources/views/welcome.blade.php ada
});

// 2. Proses Login (Wajib POST untuk menerima data form)
Route::post('/login', [AuthController::class, 'login'])->name('login');