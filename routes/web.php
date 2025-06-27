<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\UserController;
use App\Exports\SampahLogExport;
use Maatwebsite\Excel\Facades\Excel;

// --- Login & Logout ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Routing setelah login ---
Route::middleware('auth')->group(function () {

    // Dashboard (Redirect berdasarkan role)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- Menu bersama (admin & petugas) ---
    Route::get('/grafik', [DashboardController::class, 'grafik'])->name('grafik');
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');

    // --- Khusus admin ---
    Route::middleware('checkRole:admin')->group(function () {
        Route::post('/riwayat/hapus', [RiwayatController::class, 'hapus'])->name('riwayat.hapus');
        Route::get('/nonfuzzy', [DashboardController::class, 'nonfuzzy'])->name('nonfuzzy');
        Route::post('/nonfuzzy/hapus', [DashboardController::class, 'hapusNonfuzzy'])->name('nonfuzzy.hapus');
        Route::get('/export-excel', function () {
            return Excel::download(new SampahLogExport, 'data-sampah.xlsx');
        })->name('export.excel');
        Route::resource('/users', UserController::class);
         Route::get('/analisis', [\App\Http\Controllers\AnalisisController::class, 'index'])->name('analisis.index');
        Route::get('/analisis/export', [\App\Http\Controllers\AnalisisController::class, 'export'])->name('analisis.export');
    });

});
