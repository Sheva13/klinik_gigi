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
        Schema::create('list_pembelian_obat', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_pembelian')->nullable();
            $table->string('kode_obat', 10)->nullable();
            $table->integer('harga_beli')->nullable();
            $table->integer('qty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_pembelian_obat');
    }
};
