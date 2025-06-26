<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampahLogsNonfuzzyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sampah_logs_nonfuzzy', function (Blueprint $table) {
            $table->id();
            $table->float('jarakA');
            $table->float('jarakB');
            $table->float('volume')->nullable();
            $table->string('status')->nullable(); // kosong, setengah, penuh
            $table->string('rekomendasi')->nullable();
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
        Schema::dropIfExists('sampah_logs_nonfuzzy');
    }
}
