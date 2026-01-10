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
        Schema::create('mp_supply', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('driver_id')->index('driver_id');
            $table->integer('vehicle_id')->index('vehicle_id');
            $table->date('date');
            $table->integer('region_id')->index('region_id');
            $table->integer('town_id')->index('town_id');
            $table->decimal('expense', 11);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_supply');
    }
};
