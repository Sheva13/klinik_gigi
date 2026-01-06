<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Jangan lupa baris ini

class MasterPoliSeeder extends Seeder
{
    public function run()
    {
        // Data dari screenshot phpMyAdmin kamu
        $poli = [
            [
                'id' => 1,
                'kode_poli' => 'P001',
                'nama_poli' => 'Poli Gigi Umum',
                'keterangan' => null, // Di gambar terlihat kosong/null
            ],
            [
                'id' => 2,
                'kode_poli' => 'P002',
                'nama_poli' => 'Poli Spesialis Ortodonti',
                'keterangan' => null,
            ],
            [
                'id' => 3,
                'kode_poli' => 'P003',
                'nama_poli' => 'Poli Spesialis Prosthodonti',
                'keterangan' => null,
            ],
            [
                'id' => 4,
                'kode_poli' => 'P004',
                'nama_poli' => 'Poli Spesialis Periodonti',
                'keterangan' => null,
            ],
            [
                'id' => 5,
                'kode_poli' => 'P005',
                'nama_poli' => 'Poli Spesialis Penyakit Mulut',
                'keterangan' => null,
            ],
        ];

        DB::table('master_poli')->insertOrIgnore($poli);
    }
}