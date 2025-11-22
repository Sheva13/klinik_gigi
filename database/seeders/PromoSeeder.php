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
                'gambar_banner' => 'promo_merdeka.jpg', // Nanti controller kamu otomatis nambah 'uploads/'
                'tanggal_mulai' => '2024-08-01',
                'tanggal_selesai' => '2025-08-31', // Dibuat tahun depan biar statusnya AKTIF
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'judul_promo' => 'Paket Scalling Hemat',
                'deskripsi' => 'Pembersihan karang gigi (Scalling) buy 1 get 1 khusus member baru.',
                'gambar_banner' => 'promo_scalling.jpg', 
                'tanggal_mulai' => '2024-11-01',
                'tanggal_selesai' => '2025-12-31',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'judul_promo' => 'Senyum Lebaran',
                'deskripsi' => 'Bleaching gigi diskon 50% menjelang hari raya.',
                'gambar_banner' => 'https://placehold.co/600x400/png', // Contoh pakai URL luar
                'tanggal_mulai' => '2024-04-01',
                'tanggal_selesai' => null, // NULL artinya promo berlaku selamanya
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}