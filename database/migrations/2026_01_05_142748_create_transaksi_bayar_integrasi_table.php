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
        Schema::create('transaksi_bayar_integrasi', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_histori_periksa');
            $table->integer('ambil_obat')->default(0);
            $table->integer('total_tindakan')->default(0);
            $table->integer('total_obat')->default(0);
            $table->integer('total_penunjang')->default(0);
            $table->integer('total_bahan')->default(0);
            $table->integer('total_bayar')->default(0);
            $table->integer('pasien_baru')->default(0);
            $table->dateTime('waktu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_bayar_integrasi');
    }
};
