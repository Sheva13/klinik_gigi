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
        Schema::create('tindakan_pemeriksaan_integrasi', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_periksa');
            $table->integer('id_histori_periksa');
            $table->integer('tindakan');
            $table->integer('konfirmasi')->default(0);
            $table->integer('umpan_balik');
            $table->text('ket_umpan_balik');
            $table->text('action_plan')->nullable();
            $table->string('rujukan', 11);
            $table->integer('konfirmasi_post')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindakan_pemeriksaan_integrasi');
    }
};
