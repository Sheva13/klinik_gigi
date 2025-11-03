<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;

// Route bebas (guest)
Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ Route yang butuh token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route ini untuk mengambil data pasien
    Route::get('/pasien/me', [PasienController::class, 'me']);
    // Tambahkan route lain yang harus login di sini
});
