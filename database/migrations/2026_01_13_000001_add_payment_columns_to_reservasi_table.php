<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            if (!Schema::hasColumn('reservasi', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('status_pembayaran');
            }
            if (!Schema::hasColumn('reservasi', 'redirect_url')) {
                $table->string('redirect_url')->nullable()->after('snap_token');
            }
            if (!Schema::hasColumn('reservasi', 'snap_token_pelunasan')) {
                $table->string('snap_token_pelunasan')->nullable()->after('redirect_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            if (Schema::hasColumn('reservasi', 'snap_token_pelunasan')) {
                $table->dropColumn('snap_token_pelunasan');
            }
            if (Schema::hasColumn('reservasi', 'redirect_url')) {
                $table->dropColumn('redirect_url');
            }
            if (Schema::hasColumn('reservasi', 'snap_token')) {
                $table->dropColumn('snap_token');
            }
        });
    }
};