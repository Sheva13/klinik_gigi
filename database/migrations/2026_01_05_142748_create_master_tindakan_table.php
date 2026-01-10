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
        Schema::create('master_tindakan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('tindakan');
            $table->string('poli');
            $table->integer('biaya_tindakan')->default(0);
            $table->integer('jasa_medis')->default(0);
            $table->integer('jasa_perawat')->default(0);
            $table->integer('jasa_dokter')->default(0);
            $table->integer('jasa_tekniker')->default(0);
            $table->integer('jasa_radiografer')->default(0);
            $table->integer('uc')->default(0);
            $table->integer('laba')->default(0);
            $table->integer('jenis_tindakan')->nullable();
            $table->integer('persen_jasa')->default(0);
            $table->integer('asuransi')->default(0);
            $table->string('kode_jenpel', 50)->nullable();
            $table->integer('aktif')->nullable();
            $table->integer('sp')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tindakan');
    }
};
