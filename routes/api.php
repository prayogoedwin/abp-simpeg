<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

// Protected routes (pakai sanctum middleware)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/password/update', [AuthController::class, 'updatePassword']);

    Route::prefix('absensi')->group(function () {
        Route::get('/today', [AbsensiController::class, 'today']);
        Route::get('/last', [AbsensiController::class, 'last']);
        Route::post('/clock-in', [AbsensiController::class, 'clockIn']);
        Route::post('/clock-out', [AbsensiController::class, 'clockOut']);
        Route::get('/rekap', [AbsensiController::class, 'rekap']);
        Route::get('/history', [AbsensiController::class, 'history']);

        Route::post('/status-lain', [AbsensiController::class, 'storeStatusLainnya']);
    });
});