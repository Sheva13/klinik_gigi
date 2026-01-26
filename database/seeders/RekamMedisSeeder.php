<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Tambahkan ini buat tanggal

class RekamMedisSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rekam_medis')->insert([
            'id' => 2,
            // Biar sesuai format baru, kita buat agak panjang dikit, atau RM002 tetep oke
            'rekam_medis' => 'RM260110002', 
            'nama' => 'Farrel',
            'tempat_lahir' => 'Semarang',
            'tanggal_lahir' => '2006-10-02',
            'no_identitas' => '3374567890',
            'tipe_identitas' => 1,
            'status_nikah' => 1, // Pastikan tipe data di DB Integer, kalau String kasih kutip '1'
            'pekerjaan' => 1,    // Sama, cek tipe data DB
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
            
            // --- TAMBAHAN PENTING ---
            'verifikasi' => 1, // Kita set 1 biar dianggap sudah Valid oleh Admin
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}