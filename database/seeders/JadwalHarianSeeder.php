<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalHarianSeeder extends Seeder
{
    public function run()
    {
        // 1. Kita definisikan ulang aturan harinya (Sesuai Brosur)
        // Format: 'kode_dokter' => [array hari kerja]
        // 1=Senin, 7=Minggu
        $aturan_dokter = [
            'D001' => [1, 2, 3, 4, 5],       // drg. Bawa (Senin-Jumat)
            'D002' => [1, 2, 3, 4],          // drg. Aprilia (Senin-Kamis)
            'D003' => [1, 2, 3, 4, 5, 6],    // drg. Suaeni (Senin-Sabtu)
            'D004' => [1, 2, 3, 4, 5, 6],    // drg. Aulia (Senin-Sabtu)
            'D005' => [5, 6, 7],             // drg. Ghozy (Jumat-Minggu)
        ];

        $data_harian = [];
        $today = Carbon::now();

        // 2. Loop untuk 14 hari ke depan (2 minggu)
        // Supaya aplikasi kamu terlihat "hidup" dengan banyak pilihan tanggal
        for ($i = 0; $i < 14; $i++) {
            $tanggal = $today->copy()->addDays($i);
            $hari_ini = $tanggal->dayOfWeekIso; // 1 (Senin) s/d 7 (Minggu)

            // Cek setiap dokter, apakah dia kerja di "hari ini"?
            foreach ($aturan_dokter as $kode_dokter => $hari_kerja) {
                if (in_array($hari_ini, $hari_kerja)) {
                    
                    // Cari ID Master Jadwalnya (Simulasi Relasi)
                    // Kita ambil ID master jadwal berdasarkan dokter dan hari
                    $master_jadwal = DB::table('master_jadwal')
                                    ->where('kode_dokter', $kode_dokter)
                                    ->where('hari', $hari_ini)
                                    ->first();

                    // Kalau master jadwalnya ketemu, buat jadwal hariannya
                    if ($master_jadwal) {
                        $data_harian[] = [
                            'kode_jadwal' => $master_jadwal->id, // Foreign Key
                            'tanggal' => $tanggal->toDateString(), // Contoh: 2025-01-06
                            'validasi' => 'valid', // Atau sesuaikan (misal: 'buka', 1, true)
                            // 'kuota' => $master_jadwal->quota // Jika kolom kuota ada di tabel harian juga
                        ];
                    }
                }
            }
        }

        // 3. Masukkan ke database
        // Kita pakai chunk supaya kalau datanya banyak ga berat
        foreach (array_chunk($data_harian, 50) as $chunk) {
            DB::table('jadwal_harian')->insertOrIgnore($chunk);
        }
    }
}