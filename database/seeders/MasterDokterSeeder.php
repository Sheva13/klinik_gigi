<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDokterSeeder extends Seeder
{
    public function run()
    {
        // Data diambil dari brosur gambar yang diupload
        $doctors = [
            [
                'id' => 1,
                'kode_dokter' => 'D001',
                'nama' => 'drg. Bawa Adiwinarto, M.Med.Ed., Sp. Ort', //
                'gelar' => 'Sp. Ort',
                'spesialisasi' => 2, // Anggap 2 = Ortodonti
                'kode_poli' => 'P002', // Poli Ortodonti
            ],
            [
                'id' => 2,
                'kode_dokter' => 'D002',
                'nama' => 'drg. Aprilia Puspita Andarini', //
                'gelar' => 'drg',
                'spesialisasi' => 1,
                'kode_poli' => 'P001', // Poli Umum
            ],
            [
                'id' => 3,
                'kode_dokter' => 'D003',
                'nama' => 'drg. Suaeni Kurnia W', //
                'gelar' => 'drg',
                'spesialisasi' => 1,
                'kode_poli' => 'P001',
            ],
            [
                'id' => 4,
                'kode_dokter' => 'D004',
                'nama' => 'drg. Aulia Maghfira Kusuma W', //
                'gelar' => 'drg',
                'spesialisasi' => 1,
                'kode_poli' => 'P001',
            ],
            [
                'id' => 5,
                'kode_dokter' => 'D005',
                'nama' => 'drg. M. Ghozy El-Yussa', //
                'gelar' => 'drg',
                'spesialisasi' => 1,
                'kode_poli' => 'P001',
            ]
        ];

        foreach ($doctors as $doc) {
            DB::table('master_dokter')->insertOrIgnore([
                'id' => $doc['id'],
                'kode_dokter' => $doc['kode_dokter'],
                'nama' => $doc['nama'],
                'gelar' => $doc['gelar'],
                'spesialisasi' => $doc['spesialisasi'],
                'alamat' => 'Alamat Klinik Gigi', // Data dummy
                'hp' => '08123456789',            // Data dummy
                'tipe' => 1,
                'dokter_str' => 'STR-DUMMY-' . $doc['id'],
                'dokter_str_mulai' => '2024-01-01',
                'dokter_str_expire' => '2029-01-01',
                'dokter_sip' => null,
                'dokter_sip_berlaku' => null,
                'dokter_sip_expired' => null,
                'inisial' => null,
                'kode_poli' => $doc['kode_poli'],
            ]);
        }
    }
}