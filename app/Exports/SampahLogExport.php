<?php

namespace App\Exports;

use App\Models\SampahLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SampahLogExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return SampahLog::all()->map(function ($item) {
            return [
                'created_at'     => $item->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s'),
                'Jarak A'     => $item->jarakA . ' cm',
                'Jarak B'     => $item->jarakB . ' cm',
                'Volume'      => $item->volume . ' cm',
                'Status'      => $item->status,
                'Rekomendasi' => $item->rekomendasi,
            ];
        });
    }

    public function headings(): array
    {
        return ['Tanggal', 'Jarak A', 'Jarak B', 'Tinggi Sampah', 'Status', 'Rekomendasi'];
    }
}
