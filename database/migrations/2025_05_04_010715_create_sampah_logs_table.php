<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampahLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sampah_logs', function (Blueprint $table) {
            $table->id();
            $table->float('jarakA');
            $table->float('jarakB');
            $table->float('volume'); // hasil defuzzifikasi
            $table->string('status'); // Kosong, Sedang, Penuh
            $table->string('rekomendasi'); // Bersihkan / Tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sampah_logs');
    }
}
