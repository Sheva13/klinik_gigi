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
        Schema::create('odontogram', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('rekam_medis', 15);
            $table->dateTime('waktu');
            $table->string('kode_dokter', 15);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontogram');
    }
};
