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
        Schema::create('obat_pemeriksaan', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_periksa');
            $table->string('kode_obat', 10);
            $table->float('qty');
            $table->integer('tipe')->default(0)->comment('0=klinik umum;1=klinik integrasi');
            $table->integer('konfirmasi')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat_pemeriksaan');
    }
};
