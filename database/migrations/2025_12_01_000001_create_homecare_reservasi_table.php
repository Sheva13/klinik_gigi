<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homecare_reservasi', function (Blueprint $table) {
            $table->id();
            $table->string('no_pemeriksaan')->unique();
            $table->string('no_antrian')->nullable();
            $table->string('pasien_id'); 
            $table->string('rekam_medis_id')->nullable();
            $table->string('dokter_id')->nullable();
            $table->unsignedBigInteger('jadwal_id')->nullable();
            $table->date('tanggal_pesan');
            $table->time('waktu_pesan')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('keluhan')->nullable();
            
            // Financials
            $table->decimal('biaya_reservasi', 12, 2)->default(0);
            $table->decimal('biaya_transport', 12, 2)->default(0);
            $table->decimal('total_biaya_tindakan', 12, 0)->default(0); // Matches SQL decimal(12,0)
            $table->decimal('pembayaran_total', 12, 2)->default(0);
            
            // Statuses
            $table->string('metode_pembayaran')->nullable();
            $table->string('status')->nullable();
            $table->string('status_reservasi')->nullable();
            $table->string('status_booking')->default('belum_lunas');
            $table->enum('tipe_layanan', ['home_care'])->default('home_care');
            $table->enum('status_pelunasan', ['belum_lunas', 'lunas', 'gagal'])->default('belum_lunas');

            // Tokens & URLs
            $table->string('snap_token')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('snap_token_pelunasan')->nullable();

            // Additional Info
            $table->string('jenis_pasien')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->decimal('latitude', 15, 10)->nullable();
            $table->decimal('longitude', 15, 10)->nullable();
            
            // Promos & Complaints
            $table->string('jenis_keluhan')->nullable();
            $table->string('jenis_keluhan_lainnya')->nullable();
            $table->unsignedInteger('promo_id')->nullable();
            $table->decimal('potongan_promo', 15, 2)->default(0);

            $table->timestamps();

            $table->index('pasien_id');
            $table->index('dokter_id');
            $table->index('jadwal_id');
        });

        // Migrate existing data if available
        if (Schema::hasTable('reservasi')) {
            $rows = DB::table('reservasi')->where('tipe_layanan', 'home_care')->get();
            foreach ($rows as $row) {
                // Ensure columns exist in source before reading
                $statusPembayaran = property_exists($row, 'status_pembayaran') ? $row->status_pembayaran : $row->status_booking ?? 'belum_lunas';
                
                DB::table('homecare_reservasi')->insert([
                    'id' => $row->id,
                    'no_pemeriksaan' => $row->no_pemeriksaan,
                    'no_antrian' => $row->no_antrian,
                    'pasien_id' => $row->pasien_id,
                    'dokter_id' => $row->dokter_id,
                    'jadwal_id' => $row->jadwal_id,
                    'tanggal_pesan' => $row->tanggal_pesan,
                    'waktu_pesan' => $row->waktu_pesan,
                    'jam_mulai' => $row->jam_mulai,
                    'jam_selesai' => $row->jam_selesai,
                    'keluhan' => $row->keluhan,
                    'biaya_reservasi' => $row->biaya_reservasi,
                    'biaya_transport' => $row->biaya_transport,
                    'pembayaran_total' => $row->pembayaran_total,
                    'metode_pembayaran' => $row->metode_pembayaran,
                    'status' => $row->status,
                    'status_reservasi' => $row->status_reservasi,
                    'status_booking' => $statusPembayaran,
                    'jenis_pasien' => $row->jenis_pasien,
                    'alamat_lengkap' => $row->alamat_lengkap,
                    'latitude' => $row->latitude,
                    'longitude' => $row->longitude,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homecare_reservasi');
    }
};
