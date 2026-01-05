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
        Schema::create('hasil_pemeriksaan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_periksa')->index('fk_hasil_pemeriksaan_kunjungan');
            $table->text('subjek');
            $table->text('objek');
            $table->text('assessment');
            $table->text('planning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_pemeriksaan');
    }
};
