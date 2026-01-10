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
        Schema::create('master_obat', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_obat', 10)->unique('kode_obat');
            $table->string('nama', 30);
            $table->double('dosis');
            $table->integer('satuan');
            $table->integer('jenis');
            $table->integer('resep_dokter');
            $table->text('keterangan');
            $table->integer('stok')->default(0);
            $table->integer('harga_jual');

            $table->primary(['id', 'kode_obat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_obat');
    }
};
