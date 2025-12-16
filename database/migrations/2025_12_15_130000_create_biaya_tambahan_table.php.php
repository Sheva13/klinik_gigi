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
        Schema::create('biaya_tambahan', function (Blueprint $table) {
            $table->id();

            // Kolom ID yang mungkin direferensikan oleh file ALTER di proyek Anda:
            $table->bigInteger('id_periksa')->nullable(); // Kolom ini ada di file ALTER yang gagal sebelumnya
            
            // Kolom yang akan ditambahkan oleh file ALTER (kita pindahkan ke sini untuk mencegah error):
            $table->foreignId('reservasi_id')->nullable()->constrained('reservasi')->onDelete('cascade');
            
            // Data Biaya Tambahan
            $table->string('nama_biaya', 255);
            $table->text('deskripsi')->nullable();
            $table->decimal('jumlah_biaya', 10, 2);
            $table->integer('qty')->default(1); // Kuantitas (misalnya, jumlah obat)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biaya_tambahan');
    }
};