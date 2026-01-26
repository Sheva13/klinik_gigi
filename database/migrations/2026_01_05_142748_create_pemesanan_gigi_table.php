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
        Schema::create('pemesanan_gigi', function (Blueprint $table) {
            $table->integer('id_pemesanan')->primary();
            $table->string('id_pasien', 15)->nullable()->index('id_pasien');
            $table->string('jenis_gigi', 100)->nullable();
            $table->enum('status', ['Belum selesai', 'Selesai'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_gigi');
    }
};
