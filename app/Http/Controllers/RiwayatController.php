<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampahLog;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $query = SampahLog::query();

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('created_at', [
                $request->tanggal_awal . ' 00:00:00',
                $request->tanggal_akhir . ' 23:59:59'
            ]);
        }

        $logs = $query->latest()->paginate(20);

        if ($request->ajax()) {
            return view('partials.tabel-riwayat', compact('logs'))->render();
        }

        $view = auth()->user()->role === 'petugas' ? 'petugas.riwayat' : 'admin.riwayat';
        return view($view, compact('logs'));
    }

    public function hapus(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ]);

        SampahLog::whereBetween('created_at', [
            $request->tanggal_awal . ' 00:00:00',
            $request->tanggal_akhir . ' 23:59:59'
        ])->delete();

        return redirect()->back()->with('success', 'Riwayat berhasil dihapus.');
    }
}
