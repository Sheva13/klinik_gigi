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
        Schema::table('master_jadwal', function (Blueprint $table) {
            // Drop the unique composite index to allow multiple schedules per day for the same doctor
            // The default index name for unique(['kode_dokter', 'hari']) is usually 'master_jadwal_kode_dokter_hari_unique'
            $table->dropUnique(['kode_dokter', 'hari']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_jadwal', function (Blueprint $table) {
            $table->unique(['kode_dokter', 'hari']);
        });
    }
};
