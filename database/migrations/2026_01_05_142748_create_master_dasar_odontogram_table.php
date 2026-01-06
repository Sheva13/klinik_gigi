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
        Schema::create('master_dasar_odontogram', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('nama', 3);
            $table->integer('x');
            $table->integer('y');
            $table->integer('taring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_dasar_odontogram');
    }
};
