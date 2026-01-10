<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservasi', function (Blueprint $table) {
            $table->id();
            $table->string('no_pemeriksaan', 50)->nullable();
            $table->string('no_antrian', 20)->nullable(); 
            $table->string('pasien_id', 15)->nullable();  
            $table->string('dokter_id', 15)->nullable(); 
            $table->integer('jadwal_id')->nullable();
            $table->date('tanggal_pesan')->nullable();    
            $table->time('waktu_pesan')->nullable();         
            $table->time('jam_mulai')->nullable();         
            $table->time('jam_selesai')->nullable(); 
            $table->string('keluhan', 100)->nullable();
            $table->decimal('biaya_reservasi', 11, 0)->nullable();
            $table->string('status', 50)->nullable();
            $table->enum('status_reservasi', ['menunggu', 'dalam_proses', 'Selesai', 'batal'])
                  ->default('menunggu');
            $table->text('metode_pembayaran')->nullable();
            $table->enum('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'gagal'])
                  ->default('menunggu_pembayaran');   
            $table->string('bank_transaksi_id', 50)->nullable();
            $table->timestamps(); 
            $table->decimal('pembayaran_total', 12, 2)->nullable();
            $table->enum('jenis_pasien', ['Umum', 'BPJS', 'Asuransi'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};