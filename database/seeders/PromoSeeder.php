<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // PENTING: Jangan lupa ini
use Carbon\Carbon;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Masukkan beberapa data contoh
        DB::table('master_promo')->insert([
            [
                'judul_promo' => 'Promo Kemerdekaan RI',
                'deskripsi' => 'Dapatkan diskon 17% untuk semua perawatan gigi dalam rangka HUT RI.',
                'gambar_banner' => 'promo_merdeka.jpg', 
                'tanggal_mulai' => '2024-08-01',
                'tanggal_selesai' => '2025-08-31', 
                'tipe' => 'potongan_total', // Ditambahkan
                'nilai_potongan' => 17000,   // Ditambahkan (contoh)
                'harga_poin' => 50,          // Ditambahkan
                'limit_per_user' => 1,       // Ditambahkan
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'judul_promo' => 'Paket Scalling Hemat',
                'deskripsi' => 'Pembersihan karang gigi (Scalling) buy 1 get 1 khusus member baru.',
                'gambar_banner' => 'promo_scalling.jpg', 
                'tanggal_mulai' => '2024-11-01',
                'tanggal_selesai' => '2025-12-31',
                'tipe' => 'potongan_total',
                'nilai_potongan' => 10000,
                'harga_poin' => 20,
                'limit_per_user' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'judul_promo' => 'Senyum Lebaran',
                'deskripsi' => 'Bleaching gigi diskon 50% menjelang hari raya.',
                'gambar_banner' => 'https://placehold.co/600x400/png',
                'tanggal_mulai' => '2024-04-01',
                'tanggal_selesai' => null, 
                'tipe' => 'free_transport', // Contoh free transport
                'nilai_potongan' => 0,
                'harga_poin' => 100,
                'limit_per_user' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}