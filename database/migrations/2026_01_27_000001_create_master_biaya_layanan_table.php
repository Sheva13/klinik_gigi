<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_biaya_layanan', function (Blueprint $table) {
            $table->id();
            $table->string('tipe_layanan', 50); // Contoh: klinik, homecare
            $table->string('jenis_pasien', 50);  // Contoh: Umum, BPJS, Asuransi
            $table->decimal('biaya_reservasi', 12, 2); // Format mata uang
            $table->timestamps();
            
            // Menambahkan indeks untuk kolom yang sering digunakan dalam pencarian
            $table->index(['tipe_layanan', 'jenis_pasien']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_biaya_layanan');
    }
};