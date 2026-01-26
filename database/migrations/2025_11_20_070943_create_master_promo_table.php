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
      
        if (!Schema::hasTable('master_promo')) {
            Schema::create('master_promo', function (Blueprint $table) {
                
                $table->integer('id')->primary()->autoIncrement(); 
                $table->string('judul_promo', 100)->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('gambar_banner', 255)->nullable();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->string('tipe', 50)->default('potongan_total');
                $table->integer('harga_poin')->nullable();
                $table->decimal('nilai_potongan', 15, 2)->nullable();
                $table->integer('limit_per_user')->nullable()->default(null);
                $table->timestamps(); 
            }); 
        } 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_promo');
    }
};