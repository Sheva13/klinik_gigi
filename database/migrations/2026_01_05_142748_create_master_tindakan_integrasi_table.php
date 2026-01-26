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
        Schema::create('master_tindakan_integrasi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('tindakan')->nullable();
            $table->integer('id_modul_tindakan_integrasi');
            $table->integer('biaya_tindakan')->default(0);
            $table->integer('jm_s2');
            $table->integer('jm_sp');
            $table->integer('jasa_tekniker');
            $table->integer('jasa_radiografer');
            $table->integer('bhp_koas');
            $table->integer('laba_rsgm');
            $table->integer('tarif_asli');
            $table->integer('biaya_ok');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tindakan_integrasi');
    }
};
