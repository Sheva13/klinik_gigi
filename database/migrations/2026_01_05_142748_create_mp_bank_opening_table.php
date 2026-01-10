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
        Schema::create('mp_bank_opening', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('date_created');
            $table->integer('bank_id')->index('bank_id');
            $table->string('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_bank_opening');
    }
};
