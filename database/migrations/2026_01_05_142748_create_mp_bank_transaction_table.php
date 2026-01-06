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
        Schema::create('mp_bank_transaction', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('transaction_id')->index('transaction_id');
            $table->integer('bank_id')->index('bank_id');
            $table->integer('payee_id')->index('payee_id');
            $table->string('method', 50);
            $table->string('cheque_amount');
            $table->string('ref_no', 100);
            $table->integer('transaction_status');
            $table->string('transaction_type', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_bank_transaction');
    }
};
