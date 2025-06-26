<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampahLog;

class DashboardController extends Controller
{
    public function index()
    {
        $latest = SampahLog::latest()->first();
        return view(auth()->user()->role === 'petugas' ? 'petugas.dashboard' : 'admin.dashboard', compact('latest'));
    }

    public function grafik()
    {
        $data = SampahLog::orderBy('created_at', 'desc')->take(20)->get()->reverse();
        return view(auth()->user()->role === 'petugas' ? 'petugas.grafik' : 'admin.grafik', compact('data'));
    }

    public function riwayat()
    {
        $logs = SampahLog::latest()->paginate(20);
        return view(auth()->user()->role === 'petugas' ? 'petugas.riwayat' : 'admin.riwayat', compact('logs'));
    }

    public function nonfuzzy()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses hanya untuk admin.');
        }

        $logs = \App\Models\SampahLogNonFuzzy::latest()->paginate(20);
        return view('admin.nonfuzzy', compact('logs'));
    }
}
