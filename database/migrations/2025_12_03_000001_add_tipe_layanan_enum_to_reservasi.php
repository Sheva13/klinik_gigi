<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert tipe_layanan to ENUM and add to homecare_reservasi table
     */
    public function up(): void
    {
        // 1. Convert existing reservasi.tipe_layanan to ENUM
        if (Schema::hasTable('reservasi')) {
            DB::statement("ALTER TABLE reservasi MODIFY tipe_layanan ENUM('klinik', 'home_care') NOT NULL DEFAULT 'klinik'");
        }

        // 2. Add tipe_layanan to homecare_reservasi if not exists
        if (Schema::hasTable('homecare_reservasi')) {
            if (!Schema::hasColumn('homecare_reservasi', 'tipe_layanan')) {
                Schema::table('homecare_reservasi', function (Blueprint $table) {
                    $table->enum('tipe_layanan', ['home_care'])->default('home_care')->after('jenis_pasien');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert reservasi.tipe_layanan back to string
        if (Schema::hasTable('reservasi')) {
            DB::statement("ALTER TABLE reservasi MODIFY tipe_layanan VARCHAR(255)");
        }

        // 2. Drop tipe_layanan from homecare_reservasi
        if (Schema::hasTable('homecare_reservasi')) {
            Schema::table('homecare_reservasi', function (Blueprint $table) {
                $table->dropColumn('tipe_layanan');
            });
        }
    }
};
