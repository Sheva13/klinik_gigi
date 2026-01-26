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
        Schema::create('konfirmasi_daftar', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_periksa');
            $table->string('email', 25);
            $table->string('no_hp', 15);
            $table->dateTime('waktu');
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfirmasi_daftar');
    }
};
