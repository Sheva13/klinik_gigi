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
        Schema::create('mp_purchase', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('transaction_id')->index('transaction_id');
            $table->date('date');
            $table->integer('supplier_id')->index('supplier_id');
            $table->integer('store');
            $table->integer('invoice_id');
            $table->longText('description');
            $table->decimal('total_amount', 11);
            $table->string('payment_type_id', 50);
            $table->date('payment_date');
            $table->decimal('cash', 11);
            $table->string('cus_picture', 50);
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_purchase');
    }
};
