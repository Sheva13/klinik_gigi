<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterBiayaLayananSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama jika ada
        DB::table('master_biaya_layanan')->truncate();

        // Masukkan data contoh sesuai permintaan dan struktur yang ada
        DB::table('master_biaya_layanan')->insert([
            [
                'tipe_layanan' => 'klinik',
                'jenis_pasien' => 'Umum',
                'biaya_reservasi' => 25000.00,  // Sesuai dengan biaya yang digunakan di TransaksiReservasiController
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipe_layanan' => 'klinik',
                'jenis_pasien' => 'BPJS',
                'biaya_reservasi' => 20000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipe_layanan' => 'homecare',
                'jenis_pasien' => 'Umum',
                'biaya_reservasi' => 75000.00,  // Sesuai dengan permintaan user
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}