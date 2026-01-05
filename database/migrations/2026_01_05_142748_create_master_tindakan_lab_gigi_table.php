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
        Schema::create('master_tindakan_lab_gigi', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('tipe')->comment('1:prostho;2:ortho;3:sampel penelitian;4:konservasi');
            $table->string('tindakan', 50);
            $table->integer('biaya')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tindakan_lab_gigi');
    }
};
