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
        Schema::create('mp_temp_barcoder_invoice', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('barcode');
            $table->string('product_no');
            $table->integer('product_id')->index('product_id');
            $table->string('product_name');
            $table->string('mg');
            $table->decimal('price', 11);
            $table->decimal('purchase', 11);
            $table->integer('qty');
            $table->double('tax');
            $table->integer('agentid')->index('agentid');
            $table->string('source', 50);
            $table->integer('pack');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_temp_barcoder_invoice');
    }
};
