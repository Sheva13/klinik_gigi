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
        Schema::create('lab_pemeriksaan_integrasi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_periksa');
            $table->integer('id_histori_periksa');
            $table->integer('tindakan_lab');
            $table->integer('konfirmasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_pemeriksaan_integrasi');
    }
};
