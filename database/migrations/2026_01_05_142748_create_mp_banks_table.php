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
        Schema::create('mp_banks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('bankname');
            $table->string('branch', 100);
            $table->string('branchcode', 100);
            $table->string('title', 100);
            $table->string('accountno', 100);
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_banks');
    }
};
