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
        Schema::create('datamedis_umum', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('rekam_medis', 15)->index('fk_datamedis_umum_pasien');
            $table->integer('tekanan_darah');
            $table->integer('penyakit_jantung');
            $table->integer('diabetes_melitus');
            $table->integer('hemofilia');
            $table->integer('riwayat_asma');
            $table->integer('hepatitis');
            $table->integer('epilepsy');
            $table->integer('gastritis');
            $table->integer('asma');
            $table->integer('tbc');
            $table->integer('penyakit_lain');
            $table->integer('obatan');
            $table->integer('alergi_makanan');

            $table->primary(['id', 'rekam_medis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datamedis_umum');
    }
};
