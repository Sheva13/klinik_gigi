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
        Schema::create('dokter_dokumen', function (Blueprint $table) {
            $table->integer('dokumen_ids', true);
            $table->string('dokter_id', 15)->default('');
            $table->string('dokumen_no', 250)->default('Tidak Tersedia');
            $table->text('dokumen_nama');
            $table->text('dokumen_jenis');
            $table->mediumText('dokumen_link');
            $table->date('dokumen_tanggal_berlaku');
            $table->date('dokumen_tanggal_expired')->default('1999-01-01');
            $table->timestamp('dokumen_tanggal')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokter_dokumen');
    }
};
