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
        Schema::create('master_daftar_odontogram', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('singkatan', 100);
            $table->string('arti');
            $table->string('keterangan')->nullable();
            $table->integer('id_jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_daftar_odontogram');
    }
};
