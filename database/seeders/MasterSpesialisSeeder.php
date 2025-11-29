<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterSpesialisSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan tabel dulu agar tidak duplikat saat seeding ulang
        DB::table('master_spesialis')->truncate();

        // Masukkan data sesuai struktur tabel di SQL (id, gelar, nama, keterangan)
        DB::table('master_spesialis')->insert([
            [
                'id' => 1, 
                'gelar' => 'drg.', 
                'nama' => 'Dokter Gigi Umum', 
                'keterangan' => 'Poli Umum'
            ],
            [
                'id' => 2, 
                'gelar' => 'Sp.Ort', 
                'nama' => 'Spesialis Ortodonti', 
                'keterangan' => 'Meratakan Gigi'
            ],
            [
                'id' => 3, 
                'gelar' => 'Sp.BM', 
                'nama' => 'Spesialis Bedah Mulut', 
                'keterangan' => 'Operasi Gigi & Mulut'
            ],
            [
                'id' => 4, 
                'gelar' => 'Sp.KG', 
                'nama' => 'Spesialis Konservasi Gigi', 
                'keterangan' => 'Tambal & Syaraf'
            ],
            [
                'id' => 5, 
                'gelar' => 'Sp.KGA', 
                'nama' => 'Spesialis Pedodonsia', 
                'keterangan' => 'Gigi Anak'
            ],
            [
                'id' => 6, 
                'gelar' => 'Sp.Perio', 
                'nama' => 'Spesialis Periodonsia', 
                'keterangan' => 'Gusi & Penyangga Gigi'
            ],
            [
                'id' => 7, 
                'gelar' => 'Sp.Pros', 
                'nama' => 'Spesialis Prostodonsia', 
                'keterangan' => 'Gigi Tiruan'
            ],
            [
                'id' => 8, 
                'gelar' => 'Sp.PM', 
                'nama' => 'Spesialis Penyakit Mulut', 
                'keterangan' => 'Penyakit Lunak Mulut'
            ],
        ]);
    }
}