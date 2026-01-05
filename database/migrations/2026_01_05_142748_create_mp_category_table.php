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
        Schema::create('mp_category', function (Blueprint $table) {
            $table->integer('id')->index('id');
            $table->string('category_name');
            $table->string('description');
            $table->date('register_date');
            $table->integer('status');
            $table->string('added_by');

            $table->index(['id'], 'id_2');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_category');
    }
};
