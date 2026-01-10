<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalHarianSeeder extends Seeder
{
    public function run()
    {
        // Aturan hari kerja dokter
        // 1 = Senin, 7 = Minggu
        $aturan_dokter = [
            'D001' => [1, 2, 3, 4, 5],       // Senin–Jumat
            'D002' => [1, 2, 3, 4],          // Senin–Kamis
            'D003' => [1, 2, 3, 4, 5, 6],    // Senin–Sabtu
            'D004' => [1, 2, 3, 4, 5, 6],    // Senin–Sabtu
            'D005' => [5, 6, 7],             // Jumat–Minggu
        ];

        $data_harian = [];
        $today = Carbon::now();

        // Generate jadwal 14 hari ke depan
        for ($i = 0; $i < 14; $i++) {
            $tanggal = $today->copy()->addDays($i);
            $hari_ini = $tanggal->dayOfWeekIso; // 1–7

            foreach ($aturan_dokter as $kode_dokter => $hari_kerja) {
                if (in_array($hari_ini, $hari_kerja)) {

                    // Ambil master jadwal berdasarkan dokter & hari
                    $master_jadwal = DB::table('master_jadwal')
                        ->where('kode_dokter', $kode_dokter)
                        ->where('hari', $hari_ini)
                        ->first();

                    if ($master_jadwal) {
                        $data_harian[] = [
                            'kode_jadwal' => $master_jadwal->id, // Foreign Key
                            'tanggal'     => $tanggal->toDateString(),
                            'validasi'    => '1', // dokter 1 hadir jika 0 maka libur
                        ];
                    }
                }
            }
        }

        // Insert ke database (aman & ringan)
        foreach (array_chunk($data_harian, 50) as $chunk) {
            DB::table('jadwal_harian')->insertOrIgnore($chunk);
        }
    }
}
