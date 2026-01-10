<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterJadwalSeeder extends Seeder
{
    public function run()
    {
        $schedules = [];

        // 1. drg. Bawa Adiwinarto: Senin - Jumat (1-5), 17.30-22.00
        for ($hari = 1; $hari <= 5; $hari++) {
            $schedules[] = $this->buatJadwal('D001', 'P002', $hari, '17:30:00', '22:00:00');
        }

        // 2. drg. Aprilia Puspita: Senin - Kamis (1-4), 17.30-22.00
        for ($hari = 1; $hari <= 4; $hari++) {
            $schedules[] = $this->buatJadwal('D002', 'P001', $hari, '17:30:00', '22:00:00');
        }

        // 3. drg. Suaeni Kurnia: Senin - Sabtu (1-6), 12.00 - 16.00
        for ($hari = 1; $hari <= 6; $hari++) {
            $schedules[] = $this->buatJadwal('D003', 'P001', $hari, '12:00:00', '16:00:00');
        }

        // 4. drg. Aulia Maghfira: Senin - Sabtu (1-6), 08.00 - 12.00
        for ($hari = 1; $hari <= 6; $hari++) {
            $schedules[] = $this->buatJadwal('D004', 'P001', $hari, '08:00:00', '12:00:00');
        }

        // 5. drg. M. Ghozy El-Yussa (Punya 2 shift beda)
        // Shift 1: Jumat - Sabtu (5-6), 17.00 - 21.00
        for ($hari = 5; $hari <= 6; $hari++) {
            $schedules[] = $this->buatJadwal('D005', 'P001', $hari, '17:00:00', '21:00:00');
        }
        // Shift 2: Minggu (7), 10.00 - 14.00
        $schedules[] = $this->buatJadwal('D005', 'P001', 7, '10:00:00', '14:00:00');
        
        DB::table('master_jadwal')->insertOrIgnore($schedules);
    }
    private function buatJadwal($dokter, $poli, $hari, $mulai, $selesai)
    {
        return [
            'kode_dokter' => $dokter,
            'kode_poli' => $poli,
            'hari' => $hari,
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,
            'keterangan' => null,
            'quota' => 10, // Default quota
        ];
    }
}