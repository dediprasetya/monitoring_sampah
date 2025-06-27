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

    public function grafik(Request $request)
    {
        $range = $request->query('range', '1h'); // Default 1 jam terakhir
        $now = now();

        switch ($range) {
            case '5min':
                $start = $now->copy()->subMinutes(5);
                break;
            case '1h':
                $start = $now->copy()->subHour();
                break;
            case '12h':
                $start = $now->copy()->subHours(12);
                break;
            case '1d':
                $start = $now->copy()->subDay();
                break;
            case '7d':
                $start = $now->copy()->subDays(7);
                break;
            default:
                $start = $now->copy()->subHour();
        }

        $data = \App\Models\SampahLog::where('created_at', '>=', $start)
                    ->orderBy('created_at', 'asc')
                    ->get();

        $view = auth()->user()->role === 'petugas' ? 'petugas.grafik' : 'admin.grafik';
        return view($view, compact('data'));
    }


    public function riwayat()
    {
        $logs = SampahLog::latest()->paginate(20);
        return view(auth()->user()->role === 'petugas' ? 'petugas.riwayat' : 'admin.riwayat', compact('logs'));
    }

    public function nonfuzzy(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses hanya untuk admin.');
        }

        $query = \App\Models\SampahLogNonFuzzy::query();

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('created_at', [
                $request->tanggal_awal . ' 00:00:00',
                $request->tanggal_akhir . ' 23:59:59'
            ]);
        }

        $logs = $query->latest()->paginate(20);

        return view('admin.nonfuzzy', compact('logs'));
    }

    public function hapusNonfuzzy(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ]);

        \App\Models\SampahLogNonFuzzy::whereBetween('created_at', [
            $request->tanggal_awal . ' 00:00:00',
            $request->tanggal_akhir . ' 23:59:59'
        ])->delete();

        return redirect()->back()->with('success', 'Riwayat Non-Fuzzy berhasil dihapus.');
    }
}
