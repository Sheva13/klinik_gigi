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

        if (!Schema::hasTable('reservasi')) {
            Schema::create('reservasi', function (Blueprint $table) {
                
                $table->id(); 
                $table->string('no_pemeriksaan', 50)->nullable(); 
                $table->string('no_antrian', 20)->nullable(); 
                $table->string('pasien_id', 15)->nullable(); 
                $table->string('dokter_id', 15)->nullable(); 
                $table->text('keluhan')->nullable(); 
                $table->text('metode_pembayaran')->nullable(); 
                $table->integer('jadwal_id')->nullable(); 
                $table->date('tanggal_pesan')->nullable(); 
                $table->time('waktu_pesan')->nullable(); 
                $table->time('jam_mulai')->nullable(); 
                $table->time('jam_selesai')->nullable(); 
                $table->decimal('biaya_reservasi', 11, 0)->nullable(); 
                $table->decimal('pembayaran_total', 12, 2)->nullable(); 
                $table->enum('status_reservasi', ['menunggu', 'dalam_proses', 'Selesai', 'batal'])
                      ->default('menunggu'); 
                $table->enum('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'gagal'])
                      ->default('menunggu_pembayaran');
                $table->enum('jenis_pasien', ['Umum', 'BPJS', 'Asuransi'])->nullable(); 
                $table->string('bank_transaksi_id', 50)->nullable(); 
                $table->timestamp('created_at')->nullable()->useCurrent(); 
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate(); 
            });
        } 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};