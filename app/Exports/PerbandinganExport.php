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
        $fuzzyLogs = SampahLog::orderBy('created_at', 'desc')->take(100)->get();
        $nonFuzzyLogs = SampahLogNonFuzzy::orderBy('created_at', 'desc')->take(100)->get();

        return new Collection($fuzzyLogs->map(function ($fuzzy) use ($nonFuzzyLogs) {
            $non = $nonFuzzyLogs->firstWhere('created_at', $fuzzy->created_at);
            return [
                'Waktu' => $fuzzy->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s'),
                'sensor A'=> $fuzzy->jarakA . ' cm',
                'sensor B'=> $fuzzy->jarakB . ' cm',
                'Tinggi Sampah Fuzzy' => $fuzzy->volume . ' cm',
                'Status Fuzzy' => $fuzzy->status,
                'Rekomendasi Fuzzy' => $fuzzy->rekomendasi,
                'Tinggi Sampah Non-Fuzzy' => $non->volume ?? '-',
                'Status Non-Fuzzy' => $non->status ?? '-',
                'Rekomendasi Non-Fuzzy' => $non->rekomendasi ?? '-',
            ];
        }));
    }
    public function headings(): array
    {
        return ['Waktu', 'sensor A', 'sensor B', 'Tinggi Sampah Fuzzy', 'Status Fuzzy', 'Rekomendasi Fuzzy', 'Tinggi Sampah Non-Fuzzy', 'Status Non-Fuzzy', 'Rekomendasi Non-Fuzzy'];
    }
}
