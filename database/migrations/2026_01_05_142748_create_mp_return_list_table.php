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
        Schema::create('mp_return_list', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('return_id')->index('transaction_id');
            $table->string('barcode');
            $table->string('product_no');
            $table->integer('product_id')->index('medicine_id');
            $table->string('product_name');
            $table->string('mg');
            $table->decimal('price', 11);
            $table->decimal('purchase', 11);
            $table->integer('qty');
            $table->decimal('tax', 11);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_return_list');
    }
};
