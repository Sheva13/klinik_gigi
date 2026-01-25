<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DokterScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Data taken from "JADWAL PRAKTIK DOKTER 3K DENTAL CARE" image.
     */
    public function run(): void
    {
        // 1. Ensure Spesialis exist
        $spesialisData = [
            ['nama' => 'Sp. Ort', 'gelar' => 'Sp. Ort', 'keterangan' => 'Spesialis Ortodonti'],
            ['nama' => 'Sp. Perio', 'gelar' => 'Sp. Perio', 'keterangan' => 'Spesialis Periodonsia'],
            ['nama' => 'Sp. PM', 'gelar' => 'Sp. PM', 'keterangan' => 'Spesialis Penyakit Mulut'],
            ['nama' => 'Dokter Umum', 'gelar' => '', 'keterangan' => 'Dokter Gigi Umum'],
        ];

        foreach ($spesialisData as $sp) {
            DB::table('master_spesialis')->updateOrInsert(
                ['nama' => $sp['nama']],
                ['gelar' => $sp['gelar'], 'keterangan' => $sp['keterangan']]
            );
        }

        // Helper to get ID
        $getSpesialisId = fn($nama) => DB::table('master_spesialis')->where('nama', $nama)->value('id');

        // 2. Ensure Poli exist
        $poliData = [
            ['kode_poli' => 'P001', 'nama_poli' => 'Poli Gigi Umum'],
            ['kode_poli' => 'P002', 'nama_poli' => 'Poli Spesialis'],
        ];
        foreach ($poliData as $p) {
            DB::table('master_poli')->updateOrInsert(
                ['kode_poli' => $p['kode_poli']],
                ['nama_poli' => $p['nama_poli']]
            );
        }

        // 3. Doctors Data
        // Mapping image data to DB columns
        $doctors = [
            [
                'kode_dokter' => 'DRG001',
                'nama' => 'Bawa Adiwinarno',
                'gelar' => 'drg., M.Med.Ed., Sp. Ort',
                'spesialisasi' => $getSpesialisId('Sp. Ort'),
                'kode_poli' => 'P002',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Senin
                    ['hari' => 3, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Rabu
                    ['hari' => 2, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Selasa
                    ['hari' => 4, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Kamis
                    ['hari' => 5, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Jumat S1
                    ['hari' => 5, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Jumat S2
                    ['hari' => 7, 'jam_mulai' => '18:00', 'jam_selesai' => '20:00'], // Minggu
                ]
            ],
            [
                'kode_dokter' => 'DRG002',
                'nama' => 'Ayuda Nur Sukmawati',
                'gelar' => 'drg., MDSc., Sp. Perio',
                'spesialisasi' => $getSpesialisId('Sp. Perio'),
                'kode_poli' => 'P002',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Senin
                    ['hari' => 6, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Sabtu
                ]
            ],
            [
                'kode_dokter' => 'DRG003',
                'nama' => 'Aprilia Puspita Andarini',
                'gelar' => 'drg.',
                'spesialisasi' => $getSpesialisId('Dokter Umum'), // Assuming general
                'kode_poli' => 'P001',
                'schedules' => [
                    ['hari' => 2, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Selasa
                    ['hari' => 4, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Kamis
                ]
            ],
            [
                'kode_dokter' => 'DRG004',
                'nama' => 'Lisa Oktaviana Mayasari',
                'gelar' => 'drg.',
                'spesialisasi' => $getSpesialisId('Dokter Umum'),
                'kode_poli' => 'P001',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Senin
                    ['hari' => 5, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Jumat
                    ['hari' => 4, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Kamis
                ]
            ],
            [
                'kode_dokter' => 'DRG005',
                'nama' => 'Veda Sahasika A.N.',
                'gelar' => 'drg.',
                'spesialisasi' => $getSpesialisId('Dokter Umum'),
                'kode_poli' => 'P001',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Senin
                    ['hari' => 3, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Rabu
                    ['hari' => 5, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Jumat (Assumed from grouping)
                    ['hari' => 7, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Minggu (Assumed from grouping)
                    ['hari' => 6, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Sabtu
                ]
            ],
            [
                'kode_dokter' => 'DRG006',
                'nama' => 'Panky Hermawan',
                'gelar' => 'drg., Sp.PM, Subsp.Inf (K)',
                'spesialisasi' => $getSpesialisId('Sp. PM'),
                'kode_poli' => 'P002',
                'schedules' => [
                    ['hari' => 7, 'jam_mulai' => '09:00', 'jam_selesai' => '12:00'], // Minggu/On Call
                ]
            ],
            [
                'kode_dokter' => 'DRG007',
                'nama' => 'Suseni Kurnia Wirda',
                'gelar' => 'drg.',
                'spesialisasi' => $getSpesialisId('Dokter Umum'),
                'kode_poli' => 'P001',
                'schedules' => [
                    ['hari' => 4, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Kamis
                    ['hari' => 5, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Jumat
                    ['hari' => 6, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Sabtu S1
                    ['hari' => 6, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Sabtu S2
                    ['hari' => 7, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Minggu S1
                    ['hari' => 7, 'jam_mulai' => '12:00', 'jam_selesai' => '16:00'], // Minggu S2
                ]
            ],
            [
                'kode_dokter' => 'DRG008',
                'nama' => 'M. Ghazy El-Yussa',
                'gelar' => 'drg.',
                'spesialisasi' => $getSpesialisId('Dokter Umum'),
                'kode_poli' => 'P001',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Senin
                    ['hari' => 2, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Selasa S1
                    ['hari' => 2, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Selasa S2
                    ['hari' => 4, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Kamis S1
                    ['hari' => 4, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Kamis S2
                    ['hari' => 3, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Rabu S1
                    ['hari' => 3, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Rabu S2
                    ['hari' => 5, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Jumat S1
                    ['hari' => 5, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Jumat S2
                    // Sabtu is empty in image, skipped
                ]
            ],
            [
                'kode_dokter' => 'DRG009',
                'nama' => 'Nurina Khansa Vashti',
                'gelar' => 'drg.',
                'spesialisasi' => $getSpesialisId('Dokter Umum'),
                'kode_poli' => 'P001',
                'schedules' => [
                    ['hari' => 3, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Rabu
                    ['hari' => 6, 'jam_mulai' => '18:00', 'jam_selesai' => '22:00'], // Sabtu
                    ['hari' => 4, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Kamis S1
                    ['hari' => 4, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Kamis S2
                    ['hari' => 5, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Jumat
                    ['hari' => 7, 'jam_mulai' => '12:00', 'jam_selesai' => '16:00'], // Minggu S1
                    ['hari' => 7, 'jam_mulai' => '16:00', 'jam_selesai' => '20:00'], // Minggu S2
                ]
            ],
            [
                'kode_dokter' => 'DRG010',
                'nama' => 'Aulia Maghfira Kusuma W.',
                'gelar' => 'drg.',
                'spesialisasi' => $getSpesialisId('Dokter Umum'),
                'kode_poli' => 'P001',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Senin
                    ['hari' => 3, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Rabu
                    ['hari' => 6, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Sabtu
                    ['hari' => 2, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'], // Selasa S1
                    ['hari' => 2, 'jam_mulai' => '13:00', 'jam_selesai' => '17:00'], // Selasa S2
                    ['hari' => 7, 'jam_mulai' => '12:00', 'jam_selesai' => '16:00'], // Minggu S1
                    ['hari' => 7, 'jam_mulai' => '16:00', 'jam_selesai' => '20:00'], // Minggu S2
                ]
            ],
        ];

        foreach ($doctors as $d) {
            // Update or Insert Doctor
            DB::table('master_dokter')->updateOrInsert(
                ['kode_dokter' => $d['kode_dokter']],
                [
                    'nama' => $d['nama'],
                    'gelar' => $d['gelar'],
                    'spesialisasi' => $d['spesialisasi'],
                    'kode_poli' => $d['kode_poli'],
                    'alamat' => 'Semarang',
                    'hp' => '-',
                    'tipe' => 1,
                    'dokter_str' => '-',
                    'dokter_str_mulai' => '2024-01-01',
                    'dokter_str_expire' => '2029-01-01',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Clear existing schedules for this doctor to avoid duplicates with old data
            DB::table('master_jadwal')->where('kode_dokter', $d['kode_dokter'])->delete();

            // Insert new schedules
            foreach ($d['schedules'] as $s) {
                DB::table('master_jadwal')->insert([
                    'kode_dokter' => $d['kode_dokter'],
                    'kode_poli' => $d['kode_poli'],
                    'hari' => $s['hari'],
                    'jam_mulai' => $s['jam_mulai'],
                    'jam_selesai' => $s['jam_selesai'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'quota' => 10,
                    'kuota_homecare' => 5,
                ]);
            }
        }
    }
}
