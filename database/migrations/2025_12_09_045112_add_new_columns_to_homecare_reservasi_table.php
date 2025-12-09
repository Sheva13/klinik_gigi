<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            // Menambahkan kolom rekam_medis_id jika belum ada
            if (!Schema::hasColumn('homecare_reservasi', 'rekam_medis_id')) {
                // Sesuaikan tipe datanya dengan id di tabel rekam_medis (biasanya bigInteger / integer)
                $table->unsignedBigInteger('rekam_medis_id')->nullable()->after('pasien_id');
            }

            // Menambahkan kolom untuk Midtrans
            if (!Schema::hasColumn('homecare_reservasi', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('status_pembayaran');
            }
            if (!Schema::hasColumn('homecare_reservasi', 'redirect_url')) {
                $table->string('redirect_url')->nullable()->after('snap_token');
            }
        });
    }

    public function down()
    {
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            $table->dropColumn(['rekam_medis_id', 'snap_token', 'redirect_url']);
        });
    }
};