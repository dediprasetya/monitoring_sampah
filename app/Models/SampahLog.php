<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampahLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bin_id',
        'jarakA',
        'jarakB',
        'volume',
        'status',
        'rekomendasi',
    ];
}
