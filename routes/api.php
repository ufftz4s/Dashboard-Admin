<?php

use App\Http\Controllers\AttAttendanceController;
use App\Http\Controllers\AttDashboardController;
use App\Http\Controllers\AttEmployeeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth routes (existing)
Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);

// Dashboard routes
Route::get('/dashboard/stats', [AttDashboardController::class, 'stats']);
Route::get('/dashboard/weekly-attendance', [AttDashboardController::class, 'weeklyAttendance']);
Route::get('/dashboard/attendance-rates', [AttDashboardController::class, 'attendanceRates']);
Route::get('/dashboard/department-breakdown', [AttDashboardController::class, 'departmentBreakdown']);

// Employee routes
Route::get('/employees', [AttEmployeeController::class, 'index']);
Route::post('/employees', [AttEmployeeController::class, 'store']);
Route::put('/employees/{id}', [AttEmployeeController::class, 'update']);
Route::delete('/employees/{id}', [AttEmployeeController::class, 'destroy']);

// Attendance routes
Route::get('/attendances', [AttAttendanceController::class, 'index']);
Route::post('/attendances', [AttAttendanceController::class, 'store']);
Route::put('/attendances/{id}', [AttAttendanceController::class, 'update']);

// Lokasi Presensi routes
Route::get('/lokasi', [LokasiController::class, 'index']);
Route::post('/lokasi', [LokasiController::class, 'store']);
Route::put('/lokasi/{kodeLokasi}', [LokasiController::class, 'update']);
Route::delete('/lokasi/{kodeLokasi}', [LokasiController::class, 'destroy']);

// Sanctum authenticated routes (existing)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
