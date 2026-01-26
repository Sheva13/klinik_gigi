<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_jadwal', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dokter', 15);
            $table->string('kode_poli', 15);
            $table->integer('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('keterangan', 25)->nullable();
            $table->integer('quota')->nullable();
            $table->timestamps();
            $table->foreign('kode_dokter')->references('kode_dokter')->on('master_dokter')->onDelete('cascade');
            $table->foreign('kode_poli')->references('kode_poli')->on('master_poli')->onDelete('cascade');
            $table->unique(['kode_dokter', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jadwal');
    }
};