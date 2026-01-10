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
        Schema::create('pemeriksaan_awal', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_periksa');
            $table->text('keluhan')->nullable();
            $table->string('berat', 10)->nullable();
            $table->string('tinggi', 10)->nullable();
            $table->string('tensi', 10)->nullable();
            $table->text('alergi')->nullable();
            $table->string('id_perawat', 15);
            $table->text('riwayat_penyakit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_awal');
    }
};
