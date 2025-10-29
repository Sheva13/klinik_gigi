<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Route bebas (guest)
Route::get('/check', fn() => response()->json(['message' => 'API aktif']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ Route yang butuh token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Tambahkan route lain yang harus login di sini
});
