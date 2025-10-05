<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampahLog;
use App\Models\SampahLogNonFuzzy;

class DashboardController extends Controller
{
    // --- DASHBOARD ---
    public function index()
    {
        // Ambil data terbaru per bin_id
        $latestPerBin = \App\Models\SampahLog::select('bin_id')
            ->distinct()
            ->get()
            ->mapWithKeys(function($row) {
                $latest = \App\Models\SampahLog::where('bin_id', $row->bin_id)
                    ->latest()
                    ->first();
                return [$row->bin_id => $latest];
            });

        // Pilih view berdasarkan role user
        $view = auth()->user()->role === 'petugas' ? 'petugas.dashboard' : 'admin.dashboard';
        return view($view, compact('latestPerBin'));
    }

    // --- GRAFIK ---
    public function grafik(Request $request)
    {
        $range = $request->query('range', '1h');
        $binId = $request->query('bin_id'); // filter bin
        $now   = now();

        // Hitung waktu mulai berdasarkan filter range
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

        // Ambil daftar bin yang ada di database
        $availableBins = \App\Models\SampahLog::select('bin_id')
            ->distinct()
            ->pluck('bin_id')
            ->toArray();

        // Query data berdasarkan bin_id (jika dipilih)
        $query = \App\Models\SampahLog::where('created_at', '>=', $start);

        if ($binId) {
            $query->where('bin_id', $binId);
        }

        $logs = $query->orderBy('created_at', 'asc')->get();

        // Bentuk data untuk chart
        $chartData = [];
        if ($binId) {
            $chartData[$binId] = $logs;
        } else {
            foreach ($availableBins as $bin) {
                $chartData[$bin] = \App\Models\SampahLog::where('bin_id', $bin)
                    ->where('created_at', '>=', $start)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        }

        // Buat label waktu (ambil dari salah satu bin atau gabungan)
        $timeLabels = $logs->pluck('created_at')
            ->map(fn($d) => $d->setTimezone('Asia/Jakarta')->format('d/m H:i'))
            ->toArray();

        $view = auth()->user()->role === 'petugas' ? 'petugas.grafik' : 'admin.grafik';
        return view($view, compact('availableBins', 'chartData', 'timeLabels'));
    }

    // --- RIWAYAT ---
    public function riwayat(Request $request)
    {
        $binId = $request->query('bin_id');

        $query = SampahLog::orderBy('created_at', 'desc');
        if ($binId) {
            $query->where('bin_id', $binId);
        }

        $logs = $query->paginate(20);
        $bins = SampahLog::select('bin_id')->distinct()->pluck('bin_id');

        return view(auth()->user()->role === 'petugas' ? 'petugas.riwayat' : 'admin.riwayat', compact('logs', 'bins', 'binId'));
    }

    // --- NON-FUZZY (Admin only) ---
    public function nonfuzzy(Request $request)
    {
        $availableBins = \App\Models\SampahLogNonFuzzy::select('bin_id')
    ->distinct()
    ->pluck('bin_id');

    $query = \App\Models\SampahLogNonFuzzy::query();

    if ($request->filled('bin_id')) {
        $query->where('bin_id', $request->bin_id);
    }
    if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
        $query->whereBetween('created_at', [
            $request->tanggal_awal . ' 00:00:00',
            $request->tanggal_akhir . ' 23:59:59'
        ]);
    }

    $logs = $query->latest()->paginate(20);

    return view('admin.nonfuzzy', compact('logs', 'availableBins'));

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

        SampahLogNonFuzzy::whereBetween('created_at', [
            $request->tanggal_awal . ' 00:00:00',
            $request->tanggal_akhir . ' 23:59:59'
        ])->delete();

        return redirect()->back()->with('success', 'Riwayat Non-Fuzzy berhasil dihapus.');
    }
}
