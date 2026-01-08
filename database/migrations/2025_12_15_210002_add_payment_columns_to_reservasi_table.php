<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('reservasi', function (Blueprint $table) {
            // Menambahkan kolom setelah 'biaya_transport' agar rapi
            $table->decimal('total_tindakan_real', 12, 2)->default(0)->after('biaya_transport');
            $table->decimal('sisa_tagihan', 12, 2)->default(0)->after('total_tindakan_real');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('reservasi', function (Blueprint $table) {
            $table->dropColumn(['total_tindakan_real', 'sisa_tagihan']);
        });
    }
};
