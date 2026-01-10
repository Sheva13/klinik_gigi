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
        Schema::create('detail_beli', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('id_beli', 25);
            $table->string('faktur_beli', 25);
            $table->string('kode_obat', 10);
            $table->integer('harga_beli');
            $table->integer('qty');
            $table->integer('total');
            $table->date('expired');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_beli');
    }
};
