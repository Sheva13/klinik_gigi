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
        Schema::create('mp_stock', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('mid')->index('mid');
            $table->date('manufacturing');
            $table->date('expiry');
            $table->integer('qty');
            $table->string('description');
            $table->date('date');
            $table->string('added');
            $table->decimal('purchase', 11);
            $table->decimal('selling', 11);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_stock');
    }
};
