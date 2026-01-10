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
        Schema::create('mp_head', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('nature', 50);
            $table->string('type', 50);
            $table->integer('relation_id');
            $table->string('expense_type', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_head');
    }
};
