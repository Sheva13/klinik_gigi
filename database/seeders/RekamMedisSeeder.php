<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekamMedisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('rekam_medis')->insert([
           'id' => 2,
           'rekam_medis' => 'RM002',
           'nama' => 'Farrel',
           'tempat_lahir' => 'Semarang',
           'tanggal_lahir' => '2006-10-02',
           'no_identitas' => '3374567890',
           'tipe_identitas' => 1,
           'status_nikah' => 1,
           'pekerjaan' => 1,
           'alamat' => 'Semarang',
           'hp' => '081234567809',
           'golongan_darah' => 'A',
           'file_foto' => null,
           'nama_wali' => 'Kapten Baskoro',
           'hubungan_wali' => 1,
           'hp_wali' => '081234567800',
           'jenis_kelamin' => 'L',
           'jenis_pasien' => 1,
           'no_peserta' => '776589765',
           'nama_asuransi' => 'BPJS',
       ]);
    }
}
