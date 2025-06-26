<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatController;
use App\Exports\SampahLogExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Login & Logout ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Dashboard Monitoring untuk Admin & Petugas ---
// Akses hanya jika sudah login
Route::middleware('auth')->group(function () {

    // Default dashboard, bisa diatur lebih lanjut per role
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Halaman grafik monitoring
    Route::get('/grafik', [DashboardController::class, 'grafik'])->name('grafik');
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');
    Route::post('/riwayat/hapus', [RiwayatController::class, 'hapus'])->name('riwayat.hapus');
    Route::get('/nonfuzzy', [DashboardController::class, 'nonfuzzy'])->name('nonfuzzy');
    
    // Export Excel, misal hanya admin yang boleh
    Route::middleware('checkRole:admin')->group(function () {
        Route::get('/export-excel', function () {
            return Excel::download(new SampahLogExport, 'data-sampah.xlsx');
        })->name('export.excel');
        Route::resource('/users', UserController::class);
    });
});
