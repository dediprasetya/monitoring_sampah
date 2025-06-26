<?php

namespace App\Exports;

use App\Models\SampahLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SampahLogExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return SampahLog::all(['created_at', 'jarakA', 'jarakB', 'volume', 'status', 'rekomendasi']);
    }

    public function headings(): array
    {
        return ['Tanggal', 'Jarak A', 'Jarak B', 'Volume', 'Status', 'Rekomendasi'];
    }
}

