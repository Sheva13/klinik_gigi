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
        Schema::create('presensi_dokter', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('kode_dokter', 15)->nullable();
            $table->integer('tipe')->nullable();
            $table->integer('shift')->nullable();
            $table->date('tanggal')->nullable();
            $table->time('waktu')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_dokter');
    }
};
