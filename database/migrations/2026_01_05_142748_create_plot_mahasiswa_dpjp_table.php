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
        Schema::create('plot_mahasiswa_dpjp', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_plot');
            $table->string('nim', 15);
            $table->dateTime('waktu');
            $table->integer('dokter_integrasi')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_mahasiswa_dpjp');
    }
};
