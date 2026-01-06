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
        Schema::create('plot_dpjp', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('dpjp', 10);
            $table->date('tanggal');
            $table->integer('slot');
            $table->integer('status')->comment('0:non aktif;1:aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_dpjp');
    }
};
