<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('reservasi', function (Blueprint $table) {
            $table->string('tipe_layanan')->default('klinik'); // 'klinik' atau 'home_care'
            $table->text('alamat_lengkap')->nullable(); // Alamat user
            $table->double('latitude', 10, 8)->nullable(); // Koordinat user
            $table->double('longitude', 11, 8)->nullable(); // Koordinat user
            $table->decimal('jarak_km', 8, 2)->nullable(); // Hasil hitung jarak
            $table->decimal('biaya_transport', 12, 2)->default(0); // Biaya ongkir
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            //
        });
    }
};
