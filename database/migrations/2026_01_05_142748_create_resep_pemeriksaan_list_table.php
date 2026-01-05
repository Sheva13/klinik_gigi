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
        Schema::create('resep_pemeriksaan_list', function (Blueprint $table) {
            $table->integer('id_resep_list')->primary();
            $table->integer('id_periksa')->nullable();
            $table->integer('id_master_resep')->nullable();
            $table->integer('jumlah')->nullable();
            $table->integer('pemakaian')->nullable();
            $table->float('takaran')->nullable();
            $table->integer('waktu_minum')->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep_pemeriksaan_list');
    }
};
