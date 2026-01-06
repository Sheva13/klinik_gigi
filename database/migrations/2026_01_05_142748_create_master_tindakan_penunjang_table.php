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
        Schema::create('master_tindakan_penunjang', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('tindakan', 50);
            $table->text('keterangan');
            $table->integer('biaya');
            $table->integer('tipe')->comment('1:laboratorium;2:rontgen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tindakan_penunjang');
    }
};
