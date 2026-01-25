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
            // 1. Drop Foreign Key first because it likely relies on the index we want to drop
            // Default name convention: table_column_foreign -> master_jadwal_kode_dokter_foreign
            $table->dropForeign(['kode_dokter']);

            // 2. Drop the unique composite index
            // Default name: master_jadwal_kode_dokter_hari_unique
            $table->dropUnique(['kode_dokter', 'hari']);

            // 3. Add a regular index on kode_dokter so the foreign key can be re-added safely
            // (MySQL requires an index on the foreign key column)
            $table->index('kode_dokter');

            // 4. Re-add the Foreign Key
            $table->foreign('kode_dokter')
                  ->references('kode_dokter')
                  ->on('master_dokter')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_jadwal', function (Blueprint $table) {
            // Restore original state
            $table->dropForeign(['kode_dokter']);
            $table->dropIndex(['kode_dokter']);
            
            // Re-add unique index (this implicitly adds index support for FK)
            $table->unique(['kode_dokter', 'hari']);

            $table->foreign('kode_dokter')
                  ->references('kode_dokter')
                  ->on('master_dokter')
                  ->onDelete('cascade');
        });
    }
};
