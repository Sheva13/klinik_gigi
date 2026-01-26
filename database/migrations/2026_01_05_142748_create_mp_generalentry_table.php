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
        Schema::create('mp_generalentry', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('customer_id')->nullable()->default(0);
            $table->date('date');
            $table->string('naration');
            $table->string('generated_source', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_generalentry');
    }
};
