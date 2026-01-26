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
        Schema::create('obat_pemeriksaan_integrasi', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_periksa');
            $table->integer('id_histori_periksa');
            $table->integer('obat');
            $table->integer('qty');
            $table->integer('konfirmasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat_pemeriksaan_integrasi');
    }
};
