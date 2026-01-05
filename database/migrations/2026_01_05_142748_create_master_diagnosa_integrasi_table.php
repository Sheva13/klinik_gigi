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
        Schema::create('master_diagnosa_integrasi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode', 50);
            $table->string('diagnosa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_diagnosa_integrasi');
    }
};
