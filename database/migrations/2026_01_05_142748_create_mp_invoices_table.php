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
        Schema::create('mp_invoices', function (Blueprint $table) {
            $table->integer('id')->index('id');
            $table->integer('transaction_id')->index('transaction_id');
            $table->date('date');
            $table->decimal('discount', 11);
            $table->integer('status');
            $table->string('description');
            $table->string('agentname', 100);
            $table->integer('cus_id')->index('cus_id');
            $table->string('cus_picture');
            $table->string('delivered_to', 100);
            $table->string('delivered_by', 100);
            $table->date('delivered_date');
            $table->string('delivered_description');
            $table->decimal('shippingcharges', 11);
            $table->integer('prescription_id')->index('prescription_id');
            $table->integer('region_id');
            $table->integer('vehicle_id');
            $table->integer('driver_id');
            $table->integer('payment_method');
            $table->decimal('total_bill', 11);
            $table->decimal('bill_paid', 11);
            $table->integer('source');

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_invoices');
    }
};
