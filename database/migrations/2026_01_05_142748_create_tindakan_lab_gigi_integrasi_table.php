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
        Schema::create('tindakan_lab_gigi_integrasi', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_periksa');
            $table->integer('posisi_rahang')->comment('1:atas;2:bawah');
            $table->text('warna');
            $table->text('keterangan')->nullable();
            $table->dateTime('waktu_jadi');
            $table->integer('id_histori_periksa');
            $table->integer('konfirmasi')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindakan_lab_gigi_integrasi');
    }
};
