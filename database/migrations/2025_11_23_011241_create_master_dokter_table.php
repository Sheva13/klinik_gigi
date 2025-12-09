<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_dokter', function (Blueprint $table) {
            $table->id();

            $table->string('kode_dokter', 15);
            $table->string('nama', 50);
            $table->string('gelar', 50);

            // sesuai screenshot: INT, bukan varchar
            $table->integer('spesialisasi');

            // sesuai screenshot: varchar(55)
            $table->string('file_foto', 55);

            $table->string('alamat', 50);
            $table->string('hp', 15);

            // sesuai screenshot: int default 1, NOT NULL
            $table->integer('tipe')->default(1);

            $table->string('dokter_str', 250);
            $table->date('dokter_str_mulai')->default('1960-01-01');
            $table->date('dokter_str_expire')->default('1960-01-01');

            $table->string('dokter_sip', 250)->nullable();
            $table->string('dokter_sip_berlaku', 255)->nullable();
            $table->string('dokter_sip_expired', 255)->nullable();

            $table->string('inisial', 2)->nullable();
            $table->string('kode_poli', 15)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_dokter');
    }
};
