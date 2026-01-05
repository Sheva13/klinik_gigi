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
        Schema::create('mp_users', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('user_name', 50)->index('user_name');
            $table->string('user_email', 50);
            $table->string('user_address', 100);
            $table->string('user_contact_1', 50);
            $table->string('user_contact_2', 50);
            $table->string('cus_picture')->nullable();
            $table->integer('status');
            $table->string('user_description', 100);
            $table->string('user_password');
            $table->date('user_date');
            $table->string('agentname');

            $table->index(['user_name'], 'user_name_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_users');
    }
};
