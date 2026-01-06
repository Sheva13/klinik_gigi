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
        Schema::create('msmhs', function (Blueprint $table) {
            $table->string('kdpt', 6);
            $table->string('jen', 1);
            $table->string('kdprodi', 5);
            $table->string('nim', 9)->primary();
            $table->string('nama', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('msmhs');
    }
};
