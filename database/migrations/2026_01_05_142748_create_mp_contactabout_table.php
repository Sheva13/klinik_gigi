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
        Schema::create('mp_contactabout', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('contact_title');
            $table->string('contact_description');
            $table->string('phone_number');
            $table->string('address');
            $table->string('email');
            $table->string('facebook');
            $table->string('twitter');
            $table->string('linked');
            $table->string('googleplus');
            $table->string('about_title');
            $table->string('about_quotation');
            $table->string('about_name');
            $table->string('about_title2');
            $table->string('about_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_contactabout');
    }
};
