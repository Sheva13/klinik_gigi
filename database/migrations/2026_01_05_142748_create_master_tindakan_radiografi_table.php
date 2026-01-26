<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_tindakan_radiografi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('tindakan');
            $table->integer('bhp_koass');
            $table->integer('laba_rsgm');
            $table->integer('jumlah_tarif_baru');
            $table->integer('total');
            $table->integer('pembulatan_total');
            $table->integer('jm_dpjp');
            $table->integer('jm_dokter_radiologi');
            $table->integer('jm_radiografer');
            $table->integer('biaya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tindakan_radiografi');
    }
};
