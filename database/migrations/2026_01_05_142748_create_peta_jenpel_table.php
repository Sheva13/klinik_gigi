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
        Schema::create('peta_jenpel', function (Blueprint $table) {
            $table->integer('id_peta')->primary();
            $table->string('kode_jenpel', 25)->nullable();
            $table->string('elemen', 3)->nullable();
            $table->string('kode_jenpel_detail', 25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peta_jenpel');
    }
};
