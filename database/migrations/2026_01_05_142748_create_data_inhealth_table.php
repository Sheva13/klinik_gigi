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
        Schema::create('data_inhealth', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('rekam_medis', 15)->unique('norm');
            $table->string('no_peserta');
            $table->string('kode_produk', 25)->nullable()->default('');
            $table->string('kode_kelas', 25);

            $table->primary(['id', 'rekam_medis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_inhealth');
    }
};
