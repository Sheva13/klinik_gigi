<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('status_pembayaran');
            $table->string('redirect_url')->nullable()->after('snap_token');
        });
    }

    public function down(): void
    {
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'redirect_url']);
        });
    }
};
