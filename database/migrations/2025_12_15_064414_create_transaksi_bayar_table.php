<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_bayar', function (Blueprint $table) {
            $table->id();
            $table->integer('id_periksa')->index(); 
            $table->integer('ambil_obat')->default(0);  
            $table->integer('total_tindakan');   
            $table->integer('total_obat')->default(0);      
            $table->integer('total_penunjang')->default(0); 
            $table->integer('pasien_baru')->nullable();    
            $table->integer('total_bayar')->default(0);    
            $table->dateTime('waktu');                      
            $table->integer('diskon')->default(0);          
            $table->integer('total_tambahan')->default(0);  
            $table->integer('total_resep')->nullable();    
            $table->integer('biaya_qris')->nullable();     
            $table->integer('metode_bayar')->nullable();
            $table->integer('biaya_admin')->default(0)->nullable(); 
            $table->integer('biaya_admin_managecare')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_bayar');
    }
};