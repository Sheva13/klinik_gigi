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
        Schema::create('antrian_kasir', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_periksa');
            $table->date('tanggal');
            $table->integer('no_antri');
            $table->dateTime('waktu');
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrian_kasir');
    }
};
