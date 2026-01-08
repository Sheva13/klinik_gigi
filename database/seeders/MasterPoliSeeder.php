<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterPoliSeeder extends Seeder
{
    public function run()
    {
        // Data from klinikgigi3 (4).sql (lines ~1437)
        $poli = [
            [
                'id' => 1,
                'kode_poli' => 'P001',
                'nama_poli' => 'Poli Gigi Umum',
                'keterangan' => '',
            ],
            [
                'id' => 2,
                'kode_poli' => 'P002',
                'nama_poli' => 'Poli Spesialis Ortodonti',
                'keterangan' => '',
            ],
            [
                'id' => 3,
                'kode_poli' => 'P003',
                'nama_poli' => 'Poli Spesialis Prosthodonti',
                'keterangan' => '',
            ],
            [
                'id' => 4,
                'kode_poli' => 'P004',
                'nama_poli' => 'Poli Spesialis Periodonti',
                'keterangan' => '',
            ],
            [
                'id' => 5,
                'kode_poli' => 'P005',
                'nama_poli' => 'Poli Spesialis Penyakit Mulut',
                'keterangan' => '',
            ],
        ];

        DB::table('master_poli')->insertOrIgnore($poli);
    }
}