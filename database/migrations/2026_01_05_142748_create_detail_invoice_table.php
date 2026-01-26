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
        Schema::create('detail_invoice', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_invoice')->nullable();
            $table->string('perincian')->nullable();
            $table->integer('kuantiti')->nullable();
            $table->integer('harga_satuan')->nullable();
            $table->integer('jumlah')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_invoice');
    }
};
