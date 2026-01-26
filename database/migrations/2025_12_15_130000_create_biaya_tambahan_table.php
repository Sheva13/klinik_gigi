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
            // SQL: `id_periksa` int NOT NULL
            $table->integer('id_periksa'); 
            
            // SQL: `reservasi_id` bigint UNSIGNED DEFAULT NULL
            $table->unsignedBigInteger('reservasi_id')->nullable();
            
            // SQL: `homecare_reservasi_id` bigint UNSIGNED DEFAULT NULL
            $table->unsignedBigInteger('homecare_reservasi_id')->nullable();
            
            // Data Biaya Tambahan (Match SQL Dump)
            $table->text('komponen');
            $table->integer('biaya');
            $table->integer('qty');
            $table->integer('jumlah_kali');

            // SQL dump does NOT have timestamps for `biaya_tambahan`
            // $table->timestamps(); 
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