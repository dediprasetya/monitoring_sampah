<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampahLog;
use App\Models\SampahLogNonFuzzy;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PerbandinganExport;

class AnalisisController extends Controller
{
   public function index()
    {
        $fuzzyLogs = SampahLog::orderBy('created_at', 'desc')->take(50)->get();
        $nonFuzzyLogs = SampahLogNonFuzzy::orderBy('created_at', 'desc')->take(50)->get();

        $data = $fuzzyLogs->map(function ($fuzzy) use ($nonFuzzyLogs) {
            $non = $nonFuzzyLogs->firstWhere('created_at', $fuzzy->created_at);
            return [
                'waktu' => $fuzzy->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s'),
                'fuzzy_volume' => $fuzzy->volume,
                'fuzzy_status' => $fuzzy->status,
                'fuzzy_rekomendasi' => $fuzzy->rekomendasi,
                'non_volume' => $non->volume ?? '-',
                'non_status' => $non->status ?? '-',
                'non_rekomendasi' => $non->rekomendasi ?? '-',
            ];
        });

        return view('admin.analisis', ['data' => $data]);
    }


    public function export()
    {
        return Excel::download(new PerbandinganExport, 'analisis_perbandingan.xlsx');
    }
}
