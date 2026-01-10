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
        Schema::create('calon_pendaftar', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nama');
            $table->string('tempat_lahir', 25);
            $table->date('tanggal_lahir');
            $table->string('no_identitas', 25)->unique('unique_no_identitas_calon');
            $table->integer('tipe_identitas');
            $table->integer('status_nikah');
            $table->integer('pekerjaan')->comment('<option value="1">Tidak Bekerja</option>
<option value="2">PNS</option>
<option value="3">TNI / Polri</option>
<option value="4">Legislatif</option>
<option value="5">BUMN</option>
<option value="6">Swasta</option>
<option value="7">Wiraswasta</opti');
            $table->text('alamat');
            $table->string('hp', 15);
            $table->string('golongan_darah', 2);
            $table->string('file_foto', 50)->default('-');
            $table->string('nama_wali', 30);
            $table->integer('hubungan_wali');
            $table->string('hp_wali', 15);
            $table->string('jenis_kelamin', 1);
            $table->integer('status')->default(0);
            $table->integer('jenis_pasien')->nullable();
            $table->string('no_asuransi', 25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calon_pendaftar');
    }
};
