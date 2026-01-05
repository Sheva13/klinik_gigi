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
        Schema::create('master_resep', function (Blueprint $table) {
            $table->integer('id_resep', true);
            $table->string('obat', 200)->nullable();
            $table->string('sediaan', 50)->nullable();
            $table->string('dosis', 200)->nullable();
            $table->string('dosis_per_hari', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_resep');
    }
};
