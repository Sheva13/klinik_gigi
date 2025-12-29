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
        // 1. Table: homecare_reservasi
        if (!Schema::hasTable('homecare_reservasi')) {
            Schema::create('homecare_reservasi', function (Blueprint $table) {
                $table->id();
                $table->string('no_pemeriksaan')->unique();
                $table->string('no_antrian')->nullable();
                $table->unsignedBigInteger('pasien_id')->index();
                $table->string('dokter_id')->nullable()->index();
                $table->unsignedBigInteger('jadwal_id')->nullable()->index();
                
                // Waktu & Lokasi
                $table->date('tanggal_pesan');
                $table->time('waktu_pesan')->nullable();
                $table->time('jam_mulai')->nullable();
                $table->time('jam_selesai')->nullable();
                $table->text('alamat_lengkap')->nullable();
                $table->decimal('latitude', 15, 10)->nullable();
                $table->decimal('longitude', 15, 10)->nullable();

                // Detail Medis
                $table->text('keluhan')->nullable();
                $table->string('jenis_keluhan')->nullable();
                $table->string('jenis_keluhan_lainnya')->nullable();

                // Keuangan & Promo
                $table->decimal('biaya_reservasi', 12, 2)->default(0); // DP / Booking Fee
                $table->decimal('biaya_transport', 12, 2)->default(0);
                $table->decimal('total_biaya_tindakan', 12, 2)->default(0); // Biaya pelunasan
                $table->decimal('pembayaran_total', 12, 2)->default(0); // Total yang dibayar (Booking+Trans) or Full
                $table->unsignedInteger('promo_id')->nullable();
                $table->decimal('potongan_promo', 15, 2)->default(0);

                // Status & Flow
                $table->string('metode_pembayaran')->nullable();
                $table->string('status')->nullable(); // Human readable
                $table->string('status_reservasi')->nullable(); // System code (e.g. menunggu_konfirmasi)
                
                // Payment Status fields
                $table->string('status_booking')->nullable(); // Status pembayaran booking (DP)
                $table->string('status_pelunasan')->nullable(); // Status pembayaran tindakan
                $table->string('status_pembayaran')->nullable(); // Legacy field

                // Tokens
                $table->string('snap_token')->nullable();
                $table->string('snap_token_pelunasan')->nullable();

                $table->timestamps();
            });
        } else {
             // Safe Add Column if Table Exists
             Schema::table('homecare_reservasi', function (Blueprint $table) {
                if (!Schema::hasColumn('homecare_reservasi', 'snap_token_pelunasan')) {
                    $table->string('snap_token_pelunasan')->nullable()->after('snap_token');
                }
                if (!Schema::hasColumn('homecare_reservasi', 'total_biaya_tindakan')) {
                    $table->decimal('total_biaya_tindakan', 12, 2)->default(0)->after('biaya_transport');
                }
                if (!Schema::hasColumn('homecare_reservasi', 'status_pelunasan')) {
                    $table->string('status_pelunasan')->nullable()->after('status_booking');
                }
                if (!Schema::hasColumn('homecare_reservasi', 'promo_id')) {
                    $table->unsignedInteger('promo_id')->nullable()->after('biaya_reservasi');
                }
                if (!Schema::hasColumn('homecare_reservasi', 'potongan_promo')) {
                    $table->decimal('potongan_promo', 15, 2)->default(0)->after('promo_id');
                }
            });
        }

        // 2. Table: home_care_tracking
        if (!Schema::hasTable('home_care_tracking')) {
            Schema::create('home_care_tracking', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_periksa');
                $table->string('status_tracking');
                $table->text('keterangan')->nullable();
                $table->dateTime('waktu')->useCurrent();
                $table->timestamps();
            });
        }

        // 3. Update Users Table (Points)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'poin')) {
                    $table->integer('poin')->default(0)->after('email');
                }
            });
        }

        // 4. Update Master Promo (Gamification)
        if (Schema::hasTable('master_promo')) {
            Schema::table('master_promo', function (Blueprint $table) {
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
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Drop tables or columns
        // Schema::dropIfExists('homecare_reservasi');
        // Schema::dropIfExists('home_care_tracking');
    }
};
