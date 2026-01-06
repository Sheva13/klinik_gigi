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
        Schema::create('nilai_tindakan_mhs', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_tindakan');
            $table->integer('komponen');
            $table->integer('nilai');
            $table->integer('observasi')->default(1)->comment('otomatis input 1 = diobservasi, jika tidak nilai 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_tindakan_mhs');
    }
};
