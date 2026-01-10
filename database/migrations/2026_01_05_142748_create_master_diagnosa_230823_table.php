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
        Schema::create('master_diagnosa_230823', function (Blueprint $table) {
            $table->string('id', 15)->primary();
            $table->string('poli', 10);
            $table->string('diagnosa', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_diagnosa_230823');
    }
};
