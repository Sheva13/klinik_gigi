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
        Schema::create('histori_pemeriksaan_integrasi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_periksa');
            $table->dateTime('waktu');
            $table->integer('id_dokter_integrasi');
            $table->integer('id_plot');
            $table->integer('status')->default(1);
            $table->integer('status_bayar')->default(0);
            $table->integer('id_plot2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histori_pemeriksaan_integrasi');
    }
};
