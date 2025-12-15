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
            
            $table->foreignId('dokter_id')->constrained('master_dokter')->onDelete('cascade'); 
            $table->foreignId('poli_id')->constrained('master_poli')->onDelete('cascade');
            
            $table->integer('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('keterangan', 25)->nullable();
            $table->integer('quota')->nullable();
            
            $table->timestamps();
            
            $table->unique(['dokter_id', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jadwal');
    }
};