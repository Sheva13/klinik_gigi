<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add Points to Users
        if (!Schema::hasColumn('users', 'poin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('poin')->default(0)->after('email');
            });
        }

        // 2. Add Promo Types and Costs
        Schema::table('master_promo', function (Blueprint $table) {
            // Check if columns exist before adding (safety)
            if (!Schema::hasColumn('master_promo', 'tipe')) {
                $table->enum('tipe', ['potongan_total', 'free_transport'])->default('potongan_total')->after('deskripsi');
            }
            if (!Schema::hasColumn('master_promo', 'harga_poin')) {
                $table->integer('harga_poin')->default(0)->after('tipe');
            }
            if (!Schema::hasColumn('master_promo', 'nilai_potongan')) {
                $table->decimal('nilai_potongan', 15, 2)->default(0)->after('harga_poin');
            }
        });

        // 3. Add Complaint Type and Promo Usage to Reservations
        Schema::table('homecare_reservasi', function (Blueprint $table) {
            if (!Schema::hasColumn('homecare_reservasi', 'jenis_keluhan')) {
                $table->string('jenis_keluhan')->nullable()->after('status_reservasi');
            }
            if (!Schema::hasColumn('homecare_reservasi', 'jenis_keluhan_lainnya')) {
                $table->string('jenis_keluhan_lainnya')->nullable()->after('jenis_keluhan');
            }
            if (!Schema::hasColumn('homecare_reservasi', 'promo_id')) {
                $table->unsignedInteger('promo_id')->nullable()->after('biaya_reservasi');
            }
            if (!Schema::hasColumn('homecare_reservasi', 'potongan_promo')) {
                $table->decimal('potongan_promo', 15, 2)->default(0)->after('promo_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'poin')) {
                $table->dropColumn('poin');
            }
        });

        Schema::table('master_promo', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'harga_poin', 'nilai_potongan']);
        });

        Schema::table('homecare_reservasi', function (Blueprint $table) {
            $table->dropColumn(['jenis_keluhan', 'jenis_keluhan_lainnya', 'promo_id', 'potongan_promo']);
        });
    }
};
