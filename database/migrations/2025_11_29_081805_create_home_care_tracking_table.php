<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('home_care_tracking', function (Blueprint $table) {
            $table->id(); // Ini otomatis bigint unsigned auto_increment
        
            // Sesuaikan tipe data 'id_periksa' dengan 'id' di tabel reservasi (integer/bigInteger)
            $table->integer('id_periksa'); 
        
            $table->string('status_tracking', 50)->comment('assigned, otw, arrived, progress, finished');
            $table->text('keterangan')->nullable();
        
            // 'waktu' otomatis default current timestamp
            $table->dateTime('waktu')->useCurrent();
        
            $table->timestamps(); // created_at & updated_at
        
            // Index agar query riwayat cepat
            $table->index('id_periksa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_care_tracking');
    }
};
