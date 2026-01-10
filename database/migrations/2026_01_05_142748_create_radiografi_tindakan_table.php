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
        Schema::create('radiografi_tindakan', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_periksa');
            $table->text('keterangan');
            $table->text('ekspertise')->nullable();
            $table->integer('status_kirim')->comment('1:dokter;2:pasien;3:dua2nya');
            $table->date('tanggal_jadi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiografi_tindakan');
    }
};
