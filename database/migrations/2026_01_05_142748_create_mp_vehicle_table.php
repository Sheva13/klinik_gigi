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
        Schema::create('mp_vehicle', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('number');
            $table->string('vehicle_id');
            $table->string('chase_no');
            $table->string('engine_no');
            $table->date('date');
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_vehicle');
    }
};
