<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_pasien', function (Blueprint $table) {
            $table->id(); 
            
            $table->foreignId('id_jadwal')->constrained('master_jadwal'); 
            
            $table->string('rekam_medis', 15)->unique(); 
            $table->integer('no_antri');
            $table->integer('status');
            $table->integer('pasien_baru');
            $table->integer('rujukan');
            $table->integer('id_rujukan')->nullable();
            $table->integer('id_calon')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->text('followup')->nullable();
            $table->string('no_sip', 50)->nullable();
            $table->integer('biaya_admin');
            $table->integer('biaya_admin_managecare')->nullable();
            $table->text('keluhan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_pasien');
    }
};