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
        Schema::create('pembelian_obat', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('no_nota', 25)->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('banyak_jenis')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_obat');
    }
};
