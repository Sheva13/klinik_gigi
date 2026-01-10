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
        Schema::create('mp_productslist', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('category_id')->index('category_id');
            $table->string('product_name');
            $table->string('mg', 50);
            $table->integer('quantity');
            $table->decimal('purchase', 11);
            $table->decimal('retail', 11);
            $table->date('expire');
            $table->date('manufacturing');
            $table->string('sideeffects', 100);
            $table->string('description', 100);
            $table->string('barcode');
            $table->integer('min_stock');
            $table->integer('status');
            $table->integer('total_units');
            $table->string('packsize');
            $table->string('sku');
            $table->string('location');
            $table->decimal('tax', 11);
            $table->string('type');
            $table->string('image');
            $table->integer('brand_id')->index('brand_id');
            $table->integer('brand_sector_id')->index('brand_sector_id');
            $table->string('unit_type', 50)->index('unit_type');
            $table->string('net_weight', 50);
            $table->decimal('whole_sale', 11);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_productslist');
    }
};
