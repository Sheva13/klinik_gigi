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
        Schema::create('master_cuti', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('kode_dokter', 15)->nullable();
            $table->date('tgl_mulai_cuti')->nullable();
            $table->date('tgl_selesai_cuti')->nullable();
            $table->integer('aktif')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_cuti');
    }
};
