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
        Schema::create('mp_langingpage', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('companyname');
            $table->string('companydescription');
            $table->string('companykeywords');
            $table->string('logo');
            $table->string('banner');
            $table->string('slider1');
            $table->string('slider2');
            $table->string('slider3');
            $table->string('slider4');
            $table->string('slider5');
            $table->string('title1');
            $table->string('title2');
            $table->string('title3');
            $table->string('title4');
            $table->string('title5');
            $table->string('title6');
            $table->string('subtitle6');
            $table->string('subtitle6one');
            $table->string('title8');
            $table->string('title9');
            $table->string('title10');
            $table->string('currency');
            $table->string('language', 50);
            $table->string('primarycolor', 50);
            $table->string('theme_pri_hover', 50);
            $table->integer('expirey');
            $table->longText('address');
            $table->string('mobile');
            $table->string('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_langingpage');
    }
};
