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
        Schema::create('mp_supplier_payments', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('transaction_id')->index('transaction_id');
            $table->integer('supplier_id')->index('supplier_id');
            $table->decimal('amount', 11);
            $table->string('method');
            $table->date('date');
            $table->string('description');
            $table->string('agentname', 50);
            $table->integer('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_supplier_payments');
    }
};
