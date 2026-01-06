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
        Schema::create('master_posisi_odontogram', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('id_daftar_odontogram');
            $table->integer('x');
            $table->integer('y');
            $table->integer('gariske')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_posisi_odontogram');
    }
};
