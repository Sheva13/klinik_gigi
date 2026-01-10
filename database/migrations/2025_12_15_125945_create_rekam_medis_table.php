<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id();
            $table->string('rekam_medis', 15)->unique(); 
            $table->string('nama', 255);
            $table->string('tempat_lahir', 25)->nullable();
            $table->date('tanggal_lahir');
            $table->string('no_identitas', 25)->nullable()->index(); 
            $table->integer('tipe_identitas')->nullable();
            $table->integer('status_nikah')->nullable();
            $table->integer('pekerjaan')->nullable();
            $table->text('alamat');
            $table->string('hp', 15)->index(); 
            $table->string('golongan_darah', 1)->nullable(); 
            $table->string('file_foto', 50)->nullable();
            $table->string('nama_wali', 30)->nullable(); 
            $table->integer('hubungan_wali')->nullable();
            $table->string('hp_wali', 15)->nullable(); 
            $table->string('jenis_kelamin', 1)->nullable(); 
            $table->integer('jenis_pasien')->nullable();
            $table->string('no_peserta', 255)->nullable(); 
            $table->string('nama_asuransi', 255)->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};