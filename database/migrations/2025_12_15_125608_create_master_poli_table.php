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
            
            $table->string('nama_poli', 100)->unique();
            $table->string('kode_poli', 10)->unique();
            $table->text('deskripsi')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_poli');
    }
};