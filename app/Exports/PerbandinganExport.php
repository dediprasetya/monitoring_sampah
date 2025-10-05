<?php
namespace App\Exports;

use App\Models\SampahLog;
use App\Models\SampahLogNonFuzzy;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PerbandinganExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $fuzzyLogs = SampahLog::orderBy('created_at', 'desc')->take(100)->get();
        $nonFuzzyLogs = SampahLogNonFuzzy::orderBy('created_at', 'desc')->take(100)->get();

        return new Collection($fuzzyLogs->map(function ($fuzzy) use ($nonFuzzyLogs) {
            // Cari data non-fuzzy dengan bin_id sama dan waktu terdekat
            $non = $nonFuzzyLogs
                ->where('bin_id', $fuzzy->bin_id)
                ->sortBy(fn($row) => abs(strtotime($row->created_at) - strtotime($fuzzy->created_at)))
                ->first();

            return [
                'Bin'                     => $fuzzy->bin_id,
                'Waktu'                   => $fuzzy->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s'),
                'Sensor A'                => $fuzzy->jarakA . ' cm',
                'Sensor B'                => $fuzzy->jarakB . ' cm',
                'Tinggi Sampah (Fuzzy)'   => $fuzzy->volume . ' cm',
                'Status (Fuzzy)'          => $fuzzy->status,
                'Rekomendasi (Fuzzy)'     => $fuzzy->rekomendasi,
                'Tinggi Sampah (Non-Fuzzy)' => $non->volume ?? '-',
                'Status (Non-Fuzzy)'      => $non->status ?? '-',
                'Rekomendasi (Non-Fuzzy)' => $non->rekomendasi ?? '-',
            ];
        }));
    }

    public function headings(): array
    {
        return [
            'Bin',
            'Waktu',
            'Sensor A',
            'Sensor B',
            'Tinggi Sampah (Fuzzy)',
            'Status (Fuzzy)',
            'Rekomendasi (Fuzzy)',
            'Tinggi Sampah (Non-Fuzzy)',
            'Status (Non-Fuzzy)',
            'Rekomendasi (Non-Fuzzy)',
        ];
    }
}
