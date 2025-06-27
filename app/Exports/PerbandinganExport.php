<?php
namespace App\Exports;

use App\Models\SampahLog;
use App\Models\SampahLogNonFuzzy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class PerbandinganExport implements FromCollection
{
    public function collection()
    {
        $fuzzyLogs = SampahLog::orderBy('created_at', 'desc')->take(50)->get();
        $nonFuzzyLogs = SampahLogNonFuzzy::orderBy('created_at', 'desc')->take(50)->get();

        return new Collection($fuzzyLogs->map(function ($fuzzy) use ($nonFuzzyLogs) {
            $non = $nonFuzzyLogs->firstWhere('created_at', $fuzzy->created_at);
            return [
                'Waktu' => $fuzzy->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s'),
                'Tinngi Sampah Fuzzy' => $fuzzy->volume,
                'Status Fuzzy' => $fuzzy->status,
                'Rekomendasi Fuzzy' => $fuzzy->rekomendasi,
                'Tinggi Sampah Non-Fuzzy' => $non->volume ?? '-',
                'Status Non-Fuzzy' => $non->status ?? '-',
                'Rekomendasi Non-Fuzzy' => $non->rekomendasi ?? '-',
            ];
        }));
    }
}
