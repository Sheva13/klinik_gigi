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
        Schema::create('master_tindakan_copy', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('tindakan');
            $table->string('poli');
            $table->integer('biaya_tindakan');
            $table->integer('jasa_medis');
            $table->integer('jasa_perawat');
            $table->integer('jasa_dokter');
            $table->integer('jasa_tekniker');
            $table->integer('jasa_radiografer');
            $table->integer('uc');
            $table->integer('laba');
            $table->integer('jenis_tindakan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tindakan_copy');
    }
};
