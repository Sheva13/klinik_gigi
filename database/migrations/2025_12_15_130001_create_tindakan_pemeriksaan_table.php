<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindakan_pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_periksa')->nullable();
            
            // Kolom ini ditambahkan oleh ALTER yang gagal, jadi kita masukkan di sini
            $table->foreignId('reservasi_id')->nullable()->constrained('reservasi')->onDelete('cascade'); 
            
            $table->string('tindakan_nama', 255);
            $table->decimal('biaya', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindakan_pemeriksaan');
    }
};