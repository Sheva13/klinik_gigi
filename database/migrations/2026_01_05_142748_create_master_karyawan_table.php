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
        Schema::create('master_karyawan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('kode_karyawan', 15);
            $table->string('nama', 25);
            $table->string('gelar', 15);
            $table->integer('unit');
            $table->string('alamat', 25);
            $table->string('hp', 15);

            $table->primary(['id', 'kode_karyawan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_karyawan');
    }
};
