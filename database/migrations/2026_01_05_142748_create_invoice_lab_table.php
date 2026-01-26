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
        Schema::create('invoice_lab', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('tanggal')->nullable();
            $table->string('kepada')->nullable();
            $table->integer('total')->nullable();
            $table->date('tanggal_input')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lab');
    }
};
