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
        Schema::create('mp_return', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('transaction_id')->index('transaction_id');
            $table->date('date');
            $table->integer('cus_id')->index('cus_id');
            $table->string('agent');
            $table->integer('invoice_id')->index('invoice_id');
            $table->decimal('return_amount', 11);
            $table->decimal('total_bill', 11);
            $table->decimal('discount_given', 11);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_return');
    }
};
