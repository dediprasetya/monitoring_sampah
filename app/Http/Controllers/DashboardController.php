<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampahLog;
//use Maatwebsite\Excel\Facades\Excel;
//use App\Exports\SampahExport;

class DashboardController extends Controller
{
    public function index()
    {
        $latest = SampahLog::latest()->first();
        return view('dashboard', compact('latest'));
    }
    public function grafik()
    {
        $data = SampahLog::orderBy('created_at', 'desc')->take(20)->get()->reverse();
        return view('grafik', compact('data'));
    }

    //public function exportExcel()
    //{
    //    return Excel::download(new SampahExport, 'data_sampah.xlsx');
    //}
}
