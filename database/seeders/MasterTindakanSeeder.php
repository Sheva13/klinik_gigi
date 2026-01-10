<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterTindakanSeeder extends Seeder
{
    public function run()
    {
        // Sample data based on Dental Clinic context
        $tindakan = [
            // Poli Gigi Umum (P001)
            [
                'id' => 1,
                'tindakan' => 'Konsultasi Dokter Gigi',
                'poli' => 'Poli Gigi Umum',
                'biaya_tindakan' => 50000,
                'jasa_medis' => 20000,
                'jasa_perawat' => 10000,
                'jasa_dokter' => 20000,
                'jasa_tekniker' => 0,
                'jasa_radiografer' => 0,
                'uc' => 0,
                'laba' => 0,
            ],
            [
                'id' => 2,
                'tindakan' => 'Pencabutan Gigi Susu',
                'poli' => 'Poli Gigi Umum',
                'biaya_tindakan' => 150000,
                'jasa_medis' => 50000,
                'jasa_perawat' => 20000,
                'jasa_dokter' => 80000,
                'jasa_tekniker' => 0,
                'jasa_radiografer' => 0,
                'uc' => 0,
                'laba' => 0,
            ],
            [
                'id' => 3,
                'tindakan' => 'Scaling (per rahang)',
                'poli' => 'Poli Gigi Umum',
                'biaya_tindakan' => 200000,
                'jasa_medis' => 70000,
                'jasa_perawat' => 30000,
                'jasa_dokter' => 100000,
                'jasa_tekniker' => 0,
                'jasa_radiografer' => 0,
                'uc' => 0,
                'laba' => 0,
            ],
             // Poli Spesialis Ortodonti (P002)
            [
                'id' => 4,
                'tindakan' => 'Pemasangan Kawat Gigi (Metal)',
                'poli' => 'Poli Spesialis Ortodonti',
                'biaya_tindakan' => 5000000,
                'jasa_medis' => 2000000,
                'jasa_perawat' => 500000,
                'jasa_dokter' => 2500000,
                'jasa_tekniker' => 0,
                'jasa_radiografer' => 0,
                'uc' => 0,
                'laba' => 0,
            ],
            [
                'id' => 5,
                'tindakan' => 'Kontrol Ortodonti',
                'poli' => 'Poli Spesialis Ortodonti',
                'biaya_tindakan' => 250000,
                'jasa_medis' => 100000,
                'jasa_perawat' => 50000,
                'jasa_dokter' => 100000,
                'jasa_tekniker' => 0,
                'jasa_radiografer' => 0,
                'uc' => 0,
                'laba' => 0,
            ],
        ];

        DB::table('master_tindakan')->insertOrIgnore($tindakan);
    }
}
