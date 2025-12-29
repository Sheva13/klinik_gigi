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
        Schema::table('master_dokter', function (Blueprint $table) {
            // Check if column exists before adding to avoid errors if partially run
            if (!Schema::hasColumn('master_dokter', 'foto_profil')) {
                $table->string('foto_profil')->nullable()->after('spesialisasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_dokter', function (Blueprint $table) {
            $table->dropColumn('foto_profil');
        });
    }
};
