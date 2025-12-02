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
            $table->string('pasien_id'); // keep same semantics as reservasi (could be numeric id or code)
            $table->string('dokter_id')->nullable();
            $table->unsignedBigInteger('jadwal_id')->nullable();
            $table->date('tanggal_pesan');
            $table->time('waktu_pesan')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('keluhan')->nullable();
            $table->decimal('biaya_reservasi', 12, 2)->default(0);
            $table->decimal('biaya_transport', 12, 2)->default(0);
            $table->decimal('pembayaran_total', 12, 2)->default(0);
            $table->string('metode_pembayaran')->nullable();
            $table->string('status')->nullable();
            $table->string('status_reservasi')->nullable();
            $table->string('status_pembayaran')->nullable();
            $table->string('jenis_pasien')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->decimal('latitude', 15, 10)->nullable();
            $table->decimal('longitude', 15, 10)->nullable();
            $table->timestamps();

            $table->index('pasien_id');
            $table->index('dokter_id');
            $table->index('jadwal_id');
        });

        // migrate existing reservasi rows with tipe_layanan = 'home_care' into the new table
        if (Schema::hasTable('reservasi')) {
            $rows = DB::table('reservasi')->where('tipe_layanan', 'home_care')->get();
            foreach ($rows as $row) {
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
                    'status_pembayaran' => $row->status_pembayaran,
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
