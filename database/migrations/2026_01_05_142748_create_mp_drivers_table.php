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
        Schema::create('mp_drivers', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('contact', 15);
            $table->string('address');
            $table->string('lisence');
            $table->string('ref');
            $table->date('date');
            $table->string('cus_picture');
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_drivers');
    }
};
