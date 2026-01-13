<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
   
       \DB::table('users')->insert([
        
      'user_id' => 'PSN20251029661', 
      'nama_pengguna' => 'Farrel', 
      'nik' => '3374567890', 
      'rekam_medis_id' => 2, 
      'tanggal_lahir' => '2006-10-02', 
      'jenis_kelamin' => 'L', 
      'file_foto' => null, 
      'no_hp' => '081234567809', 
      'email' => 'farrelsheva@gmail.com', 
      'password' => bcrypt('password123'), 
      'current_token' => '71|PEFNQztzamR5FWe1nIQ6MDJ3wUoNhJCSHbtBmDeV30ef1c6f',
      'alamat' => 'Jl. Merpati No. 45, Surabaya',
       ]);
   //
    }
}
