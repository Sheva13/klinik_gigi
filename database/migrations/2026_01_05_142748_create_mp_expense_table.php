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
        Schema::create('mp_expense', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('transaction_id')->index('transaction_id');
            $table->integer('head_id')->index('head_id');
            $table->string('total_bill');
            $table->string('total_paid');
            $table->date('date');
            $table->string('user');
            $table->string('method', 50);
            $table->longText('description');
            $table->integer('payee_id')->index('payee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_expense');
    }
};
