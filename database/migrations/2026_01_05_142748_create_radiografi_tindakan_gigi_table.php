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
        Schema::create('radiografi_tindakan_gigi', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_periksa');
            $table->integer('nomor_gigi');
            $table->integer('tindakan');
            $table->integer('grup')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiografi_tindakan_gigi');
    }
};
