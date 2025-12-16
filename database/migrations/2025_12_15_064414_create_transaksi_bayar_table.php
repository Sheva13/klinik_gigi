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
 
        if (!Schema::hasTable('transaksi_bayar')) {
            Schema::create('transaksi_bayar', function (Blueprint $table) {
                
                $table->id(); 

                $table->integer('id_periksa'); 
                $table->integer('ambil_obat')->default(0); 
                $table->integer('total_tindakan')->default(0); 
                $table->integer('total_obat')->default(0); 
                $table->integer('total_penunjang')->default(0); 
                $table->integer('total_bayar')->default(0); 
                $table->integer('diskon')->default(0); 
                $table->integer('total_tambahan')->default(0); 

                $table->integer('pasien_baru')->nullable(); 
                $table->integer('total_resep')->nullable(); 
                $table->integer('biaya_qris')->nullable(); 
                $table->integer('metode_bayar')->nullable(); 
                $table->integer('biaya_admin')->default(0); 
                $table->integer('biaya_admin_managecare')->nullable(); 

                $table->dateTime('waktu'); 

                $table->timestamp('created_at')->nullable(); 
                $table->timestamp('updated_at')->nullable(); 
                
            });
        } 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_bayar');
    }
};