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
        Schema::create('dokumen_penunjang', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_periksa')->nullable();
            $table->string('nama_file')->nullable();
            $table->string('path')->nullable();
            $table->string('rekam_medis')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_penunjang');
    }
};
