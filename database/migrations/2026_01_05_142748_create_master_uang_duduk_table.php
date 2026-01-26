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
        Schema::create('master_uang_duduk', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_dokter', 15)->nullable();
            $table->integer('pagisiang_weekday')->nullable();
            $table->integer('malam')->nullable();
            $table->integer('pagisiang_weekend')->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_uang_duduk');
    }
};
