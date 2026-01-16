<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoAntrianToHomecareReservasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            $table->integer('no_antrian')->nullable()->after('status_reservasi');
            // Index for faster lookup of max queue based on schedule & date
            $table->index(['jadwal_id', 'tanggal_pesan']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            $table->dropIndex(['jadwal_id', 'tanggal_pesan']);
            $table->dropColumn('no_antrian');
        });
    }
}
