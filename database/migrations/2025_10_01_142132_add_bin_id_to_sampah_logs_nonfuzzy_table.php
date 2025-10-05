<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBinIdToSampahLogsNonfuzzyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sampah_logs_nonfuzzy', function (Blueprint $table) {
            $table->string('bin_id')->after('id')->default('bin1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('sampah_logs_nonfuzzy', function (Blueprint $table) {
            $table->dropColumn('bin_id');
        });
    }
}
