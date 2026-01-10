<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_poli', function (Blueprint $table) {
            $table->id(); 
            $table->string('kode_poli', 15)->unique();       
            $table->string('nama_poli', 100)->unique();
            $table->text('keterangan')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_poli');
    }
};