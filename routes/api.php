<?php

use App\Http\Controllers\AttAttendanceController;
use App\Http\Controllers\AttDashboardController;
use App\Http\Controllers\AttEmployeeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\JadwalKerjaController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PerizinanController;
use App\Http\Controllers\TipeController;
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

// Perizinan (Permission/Leave) routes
Route::get('/perizinan', [PerizinanController::class, 'index']);
Route::post('/perizinan', [PerizinanController::class, 'store']);
Route::get('/perizinan/{id}', [PerizinanController::class, 'show']);
Route::put('/perizinan/{id}', [PerizinanController::class, 'update']);
Route::patch('/perizinan/{id}/approve', [PerizinanController::class, 'approve']);
Route::patch('/perizinan/{id}/reject', [PerizinanController::class, 'reject']);

// Tipe izin (Permission types) routes
Route::get('/tipe-izin', [TipeController::class, 'index']);

// Jadwal kerja (Work schedule) routes
Route::get('/jadwal-kerja', [JadwalKerjaController::class, 'index']);
Route::post('/jadwal-kerja', [JadwalKerjaController::class, 'store']);
Route::put('/jadwal-kerja/{id}', [JadwalKerjaController::class, 'update']);
Route::delete('/jadwal-kerja/{id}', [JadwalKerjaController::class, 'destroy']);

// Pegawai (for Admin Panel - Add Employee)
Route::get('/pegawai', [PegawaiController::class, 'index']);
Route::post('/pegawai', [PegawaiController::class, 'store']);

// Sanctum authenticated routes (existing)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
