<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampahLogNonFuzzy extends Model
{
    use HasFactory;

    protected $table = 'sampah_logs_nonfuzzy';
    protected $fillable = ['jarakA', 'jarakB', 'volume', 'status', 'rekomendasi'];
}
