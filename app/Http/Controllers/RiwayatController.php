<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampahLog;

class RiwayatController extends Controller
{
    public function index()
    {
        $logs = SampahLog::latest()->paginate(20);
        return view(auth()->user()->role === 'petugas' ? 'petugas.riwayat' : 'admin.riwayat', compact('logs'));
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
            $request->tanggal_awal,
            $request->tanggal_akhir
        ])->delete();

        return redirect()->back()->with('success', 'Riwayat berhasil dihapus.');
    }
}
