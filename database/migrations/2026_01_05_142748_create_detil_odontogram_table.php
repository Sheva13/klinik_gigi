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
        Schema::create('detil_odontogram', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_odontogram');
            $table->integer('id_gigi');
            $table->integer('id_daftar_odontogram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detil_odontogram');
    }
};
