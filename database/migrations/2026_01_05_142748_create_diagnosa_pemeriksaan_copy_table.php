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
        Schema::create('diagnosa_pemeriksaan_copy', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_periksa');
            $table->integer('id_diagnosa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosa_pemeriksaan_copy');
    }
};
