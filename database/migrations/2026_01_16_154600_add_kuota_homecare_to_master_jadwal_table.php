<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKuotaHomecareToMasterJadwalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_jadwal', function (Blueprint $table) {
            $table->integer('kuota_homecare')->default(0)->after('quota')->comment('Kuota khusus layanan HomeCare');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_jadwal', function (Blueprint $table) {
            $table->dropColumn('kuota_homecare');
        });
    }
}
