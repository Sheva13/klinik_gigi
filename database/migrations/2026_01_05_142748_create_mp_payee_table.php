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
        Schema::create('mp_payee', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('customer_name', 50);
            $table->string('cus_email', 50);
            $table->string('cus_password');
            $table->string('cus_address');
            $table->string('cus_contact_1', 50);
            $table->string('cus_contact_2', 50);
            $table->string('cus_company', 50);
            $table->string('cus_description', 100);
            $table->string('cus_picture', 100);
            $table->integer('cus_status');
            $table->string('cus_region');
            $table->string('cus_town');
            $table->string('cus_type', 50);
            $table->string('cus_balance');
            $table->date('cus_date');
            $table->string('customer_nationalid');
            $table->string('type', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_payee');
    }
};
