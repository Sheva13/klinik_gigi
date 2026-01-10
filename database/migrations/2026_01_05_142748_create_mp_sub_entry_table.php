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
        Schema::create('mp_sub_entry', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('parent_id')->index('sid');
            $table->integer('accounthead')->index('accounthead');
            $table->decimal('amount', 11)->index('amount');
            $table->integer('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_sub_entry');
    }
};
