<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;
use App\Models\MasterPoli;
use Carbon\Carbon;

class UpdateJadwalDokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Data Poli Ada
        $poliUmum = MasterPoli::firstOrCreate(
            ['kode_poli' => 'P001'],
            ['nama_poli' => 'Poli Gigi Umum', 'keterangan' => 'Layanan Gigi Umum']
        );

        $poliOrtho = MasterPoli::firstOrCreate(
            ['kode_poli' => 'P002'],
            ['nama_poli' => 'Poli Spesialis Ortodonti', 'keterangan' => 'Layanan Kawat Gigi']
        );

        // Data Poli ID (Assuming ID auto increment/exists)
        // Kita butuh kode_poli untuk relasi dokter jadwal

        // 2. Data Dokter & Jadwal (Array)
        $doctors = [
            [
                'nama' => 'drg. Bawa Adiwinarno, M., Med.Ed., Sp.Ort',
                'gelar' => 'Sp.Ort',
                'spesialisasi' => 'Ortodonti',
                'kode_dokter' => 'DR001', // Example Code
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P002'], // Senin (Ortho & Umum - Priority Ortho)
                    ['hari' => 2, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P002'], // Selasa
                    ['hari' => 3, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P002'], // Rabu
                    ['hari' => 4, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P002'], // Kamis
                    ['hari' => 5, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P002'], // Jumat
                    ['hari' => 6, 'jam_mulai' => '08:00:00', 'jam_selesai' => '10:00:00', 'poli' => 'P002'], // Sabtu
                    ['hari' => 7, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'], // Minggu (Umum)
                    ['hari' => 7, 'jam_mulai' => '13:00:00', 'jam_selesai' => '17:00:00', 'poli' => 'P002'], // Minggu (Ortho)
                ]
            ],
            [
                'nama' => 'drg. Aprilia Puspita Anda',
                'gelar' => '-',
                'spesialisasi' => 'Umum',
                'kode_dokter' => 'DR002',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P001'],
                    ['hari' => 2, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P001'], // Merged 18:00-22:00 into this block or treat as one shift
                    ['hari' => 3, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P001'],
                    ['hari' => 4, 'jam_mulai' => '17:30:00', 'jam_selesai' => '22:00:00', 'poli' => 'P001'],
                    ['hari' => 6, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 7, 'jam_mulai' => '10:00:00', 'jam_selesai' => '14:00:00', 'poli' => 'P001'],
                ]
            ],
            [
                'nama' => 'drg. Aulia Maghfira Kusuma W',
                'gelar' => '-',
                'spesialisasi' => 'Umum',
                'kode_dokter' => 'DR003',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 2, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 3, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 4, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 5, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 6, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 7, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                ]
            ],
            [
                'nama' => 'drg. Suaeni Kurnia W',
                'gelar' => '-',
                'spesialisasi' => 'Umum',
                'kode_dokter' => 'DR004',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 2, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 3, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 4, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 5, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 6, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                ]
            ],
            [
                'nama' => 'drg. M. Ghozy El-Yussa',
                'gelar' => '-',
                'spesialisasi' => 'Umum',
                'kode_dokter' => 'DR005',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 2, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 3, 'jam_mulai' => '17:30:00', 'jam_selesai' => '21:30:00', 'poli' => 'P001'],
                    ['hari' => 4, 'jam_mulai' => '17:00:00', 'jam_selesai' => '21:00:00', 'poli' => 'P001'],
                    ['hari' => 5, 'jam_mulai' => '17:00:00', 'jam_selesai' => '21:00:00', 'poli' => 'P001'],
                    ['hari' => 6, 'jam_mulai' => '17:00:00', 'jam_selesai' => '21:00:00', 'poli' => 'P001'],
                    ['hari' => 7, 'jam_mulai' => '10:00:00', 'jam_selesai' => '14:00:00', 'poli' => 'P001'],
                ]
            ],
            [
                'nama' => 'drg. Lisa Oktaviana',
                'gelar' => '-',
                'spesialisasi' => 'Umum',
                'kode_dokter' => 'DR006',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '18:00:00', 'jam_selesai' => '22:00:00', 'poli' => 'P001'],
                    ['hari' => 2, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 2, 'jam_mulai' => '13:00:00', 'jam_selesai' => '17:00:00', 'poli' => 'P001'],
                    ['hari' => 4, 'jam_mulai' => '13:00:00', 'jam_selesai' => '17:00:00', 'poli' => 'P001'],
                    ['hari' => 5, 'jam_mulai' => '17:00:00', 'jam_selesai' => '21:00:00', 'poli' => 'P001'],
                    ['hari' => 6, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 7, 'jam_mulai' => '13:00:00', 'jam_selesai' => '17:00:00', 'poli' => 'P001'],
                ]
            ],
            [
                'nama' => 'drg. Nurina Khansa Vashti',
                'gelar' => '-',
                'spesialisasi' => 'Umum',
                'kode_dokter' => 'DR007',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '12:00:00', 'jam_selesai' => '17:00:00', 'poli' => 'P001'],
                    ['hari' => 2, 'jam_mulai' => '13:00:00', 'jam_selesai' => '17:00:00', 'poli' => 'P001'],
                    ['hari' => 3, 'jam_mulai' => '12:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 4, 'jam_mulai' => '09:00:00', 'jam_selesai' => '13:00:00', 'poli' => 'P001'],
                    ['hari' => 5, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 6, 'jam_mulai' => '13:00:00', 'jam_selesai' => '17:00:00', 'poli' => 'P001'],
                    ['hari' => 7, 'jam_mulai' => '15:00:00', 'jam_selesai' => '19:00:00', 'poli' => 'P001'],
                ]
            ],
            [
                'nama' => 'drg. Veda Sahasika',
                'gelar' => '-',
                'spesialisasi' => 'Umum',
                'kode_dokter' => 'DR008',
                'schedules' => [
                    ['hari' => 1, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 2, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 3, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 5, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                    ['hari' => 6, 'jam_mulai' => '13:00:00', 'jam_selesai' => '16:00:00', 'poli' => 'P001'],
                    ['hari' => 7, 'jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00', 'poli' => 'P001'],
                ]
            ],
            [
                'nama' => 'drg. Ayuda Nur Sukmawati, MDSc., Sp.Perio',
                'gelar' => 'Sp.Perio',
                'spesialisasi' => 'Periodonti',
                'kode_dokter' => 'DR009',
                'schedules' => [
                    ['hari' => 2, 'jam_mulai' => '18:00:00', 'jam_selesai' => '22:00:00', 'poli' => 'P001'], // Masuk poli umum sesuai gambar
                    ['hari' => 3, 'jam_mulai' => '18:00:00', 'jam_selesai' => '22:00:00', 'poli' => 'P001'],
                    ['hari' => 4, 'jam_mulai' => '18:00:00', 'jam_selesai' => '22:00:00', 'poli' => 'P001'],
                    ['hari' => 6, 'jam_mulai' => '13:00:00', 'jam_selesai' => '17:00:00', 'poli' => 'P001'],
                    ['hari' => 7, 'jam_mulai' => '14:00:00', 'jam_selesai' => '18:00:00', 'poli' => 'P001'],
                ]
            ],
        ];

        DB::beginTransaction();
        try {
            // 3. Loop dokter, update atau create, lalu update jadwal
            foreach ($doctors as $docData) {
                // Update or Create Dokter
                $dokter = MasterDokter::updateOrCreate(
                    ['kode_dokter' => $docData['kode_dokter']],
                    [
                        'nama' => $docData['nama'],
                        'gelar' => $docData['gelar'],
                        'spesialisasi' => $docData['spesialisasi'],
                        'kode_poli' => 'P001', // Default Poli Base
                        // Fill dummy required fields if creating new
                        'alamat' => 'Yogyakarta',
                        'hp' => '08123456789',
                        'dokter_str' => '-',
                        'dokter_sip' => '-',
                        'inisial' => substr($docData['nama'], 0, 2),
                    ]
                );

                // Hapus jadwal lama dokter ini
                MasterJadwal::where('kode_dokter', $dokter->kode_dokter)->delete();

                // Insert Jadwal Baru
                foreach ($docData['schedules'] as $schedule) {
                    MasterJadwal::create([
                        'kode_dokter' => $dokter->kode_dokter,
                        'kode_poli' => $schedule['poli'],
                        'hari' => $schedule['hari'],
                        'jam_mulai' => $schedule['jam_mulai'],
                        'jam_selesai' => $schedule['jam_selesai'],
                        'quota' => 10, // Default quota
                        'keterangan' => '-',
                    ]);
                }
            }

            DB::commit();
            $this->command->info('Berhasil mengupdate 9 dokter dan jadwalnya!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal: ' . $e->getMessage());
        }
    }
}
