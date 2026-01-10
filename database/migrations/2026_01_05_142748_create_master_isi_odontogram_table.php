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
        Schema::create('master_isi_odontogram', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_daftar_odontogram');
            $table->float('ketebalan')->nullable();
            $table->boolean('warna')->nullable();
            $table->boolean('isi')->nullable();
            $table->boolean('arsiran')->nullable();
            $table->string('tulisan', 100)->nullable();
            $table->boolean('garis')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_isi_odontogram');
    }
};
