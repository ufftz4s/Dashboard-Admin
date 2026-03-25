<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardStatsController;
use App\Http\Controllers\LogPresensiController;
use App\Http\Controllers\PegawaiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);
Route::get('/dashboard/stats', [DashboardStatsController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/pegawai', [PegawaiController::class, 'index']);
    Route::post('/pegawai', [PegawaiController::class, 'store']);
    Route::put('/pegawai/{id}', [PegawaiController::class, 'update']);
    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy']);
    Route::get('/presensi', [LogPresensiController::class, 'index']);
});
