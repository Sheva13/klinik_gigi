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
        Schema::create('dokumen_persetujuan_tindakan', function (Blueprint $table) {
            $table->integer('persetujuan_ids', true);
            $table->text('persetujuan_nama');
            $table->integer('persetujuan_umur')->default(0);
            $table->string('persetujuan_jenis_kelamin', 50)->default('');
            $table->text('persetujuan_alamat');
            $table->text('persetujuan_hasil');
            $table->text('persetujuan_tindakan');
            $table->text('persetujuan_anestesi');
            $table->text('persetujuan_terhadap');
            $table->text('persetujuan_nama_pasien');
            $table->integer('persetujuan_umur_pasien')->default(0);
            $table->string('persetujuan_jenis_kelamin_pasien', 50)->default('');
            $table->text('persetujuan_alamat_pasien');
            $table->text('persetujuan_nama_saksi');
            $table->longText('persetujuan_ttd_dokter');
            $table->longText('persetujuan_ttd_ybs');
            $table->longText('persetujuan_ttd_saksi');
            $table->string('dokter_kode', 250);
            $table->timestamp('persetujuan_tgl')->useCurrent();
            $table->string('persetujuan_jenis_tindakan', 150)->nullable();
            $table->string('rekam_medis', 150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_persetujuan_tindakan');
    }
};
