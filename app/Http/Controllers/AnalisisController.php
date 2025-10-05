<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampahLog;
use App\Models\SampahLogNonFuzzy;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PerbandinganExport;

class AnalisisController extends Controller
{
    public function index(Request $request)
    {
        // Ambil daftar bin unik
        $availableBins = SampahLog::select('bin_id')->distinct()->pluck('bin_id');

        $queryFuzzy = SampahLog::query();
        $queryNonFuzzy = SampahLogNonFuzzy::query();

        // Filter berdasarkan bin_id jika dipilih
        if ($request->filled('bin_id')) {
            $queryFuzzy->where('bin_id', $request->bin_id);
            $queryNonFuzzy->where('bin_id', $request->bin_id);
        }

        // Ambil data fuzzy dengan pagination
        $fuzzyLogs = $queryFuzzy->orderBy('created_at', 'desc')->paginate(20);
        $nonFuzzyLogs = $queryNonFuzzy->orderBy('created_at', 'desc')->get();

        // Transformasi ke format perbandingan
        $mapped = $fuzzyLogs->map(function ($fuzzy) use ($nonFuzzyLogs) {
            $non = $nonFuzzyLogs->where('created_at', $fuzzy->created_at)->first();
            return [
                'bin_id'            => $fuzzy->bin_id,
                'waktu'             => $fuzzy->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                'fuzzy_volume'      => $fuzzy->volume,
                'fuzzy_status'      => $fuzzy->status,
                'fuzzy_rekomendasi' => $fuzzy->rekomendasi,
                'non_volume'        => $non->volume ?? '-',
                'non_status'        => $non->status ?? '-',
                'non_rekomendasi'   => $non->rekomendasi ?? '-',
            ];
        });

        // Bungkus ulang jadi paginator
        $data = new LengthAwarePaginator(
            $mapped,
            $fuzzyLogs->total(),
            $fuzzyLogs->perPage(),
            $fuzzyLogs->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.analisis', [
            'data' => $data,
            'availableBins' => $availableBins,
        ]);
    }

    public function export()
    {
        return Excel::download(new PerbandinganExport, 'analisis_perbandingan.xlsx');
    }
}
