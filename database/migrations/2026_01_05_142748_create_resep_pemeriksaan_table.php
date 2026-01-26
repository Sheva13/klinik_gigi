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
        Schema::create('resep_pemeriksaan', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_periksa');
            $table->text('resep')->nullable();
            $table->integer('obat_konsultasi')->nullable();
            $table->integer('id_master_resep')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep_pemeriksaan');
    }
};
