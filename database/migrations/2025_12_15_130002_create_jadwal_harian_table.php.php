<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_harian', function (Blueprint $table) {
            $table->id();
            
            // Contoh struktur yang dibutuhkan
            $table->foreignId('master_jadwal_id')->constrained('master_jadwal')->onDelete('cascade');
            $table->date('tanggal');
            $table->boolean('is_libur')->default(false);
            
            // Kolom timestamps yang coba ditambahkan oleh ALTER yang gagal
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_harian');
    }
};