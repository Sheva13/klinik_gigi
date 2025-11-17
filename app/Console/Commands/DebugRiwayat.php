<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MpUser;
use App\Models\RekamMedis;
use App\Models\Reservasi;

class DebugRiwayat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:riwayat {--user_id=} {--rekam-medis-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug riwayat data untuk user tertentu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user_id');
        $rekamMedisId = $this->option('rekam-medis-id');

        if (!$userId && !$rekamMedisId) {
            $this->error('Gunakan: php artisan debug:riwayat --user_id=PSN20251105123');
            return;
        }

        $this->info('======== DEBUG RIWAYAT ========');

        // Step 1: Cek User
        $this->line("\n📋 STEP 1: Cek User");
        if ($userId) {
            $user = MpUser::where('user_id', $userId)->first();
        } else {
            $user = MpUser::where('rekam_medis_id', $rekamMedisId)->first();
        }

        if (!$user) {
            $this->error('❌ User tidak ditemukan');
            return;
        }

        $this->info('✅ User ditemukan:');
        $this->table(['Field', 'Value'], [
            ['User ID', $user->user_id],
            ['Nama', $user->nama_pengguna],
            ['Email', $user->email],
            ['Rekam Medis ID (FK)', $user->rekam_medis_id],
        ]);

        // Step 2: Cek Rekam Medis
        $this->line("\n📋 STEP 2: Cek Rekam Medis");
        if (!$user->rekam_medis_id) {
            $this->error('❌ User tidak punya rekam_medis_id');
            return;
        }

        $rekamMedis = RekamMedis::find($user->rekam_medis_id);
        if (!$rekamMedis) {
            $this->error('❌ Rekam Medis tidak ditemukan di database');
            return;
        }

        $this->info('✅ Rekam Medis ditemukan:');
        $this->table(['Field', 'Value'], [
            ['ID', $rekamMedis->id],
            ['Nomor Rekam Medis', $rekamMedis->rekam_medis],
            ['Nama', $rekamMedis->nama],
            ['Tanggal Lahir', $rekamMedis->tanggal_lahir],
            ['Jenis Kelamin', $rekamMedis->jenis_kelamin],
        ]);

        // Step 3: Cek Reservasi
        $this->line("\n📋 STEP 3: Cek Reservasi (menggunakan filter: pasien_id = '{$rekamMedis->rekam_medis}')");
        $reservasi = Reservasi::where('pasien_id', $rekamMedis->rekam_medis)
            ->orderBy('tanggal_pesan', 'desc')
            ->get();

        if ($reservasi->count() === 0) {
            $this->warn('⚠️  Tidak ada reservasi untuk user ini');
        } else {
            $this->info("✅ Ditemukan {$reservasi->count()} reservasi:");
            
            $rows = $reservasi->map(function ($item) {
                return [
                    'No. Pemeriksaan' => $item->no_pemeriksaan,
                    'Dokter' => $item->dokter?->nama ?? 'N/A',
                    'Tanggal' => $item->tanggal_pesan,
                    'Status' => $item->status_reservasi,
                ];
            })->toArray();

            foreach ($rows as $row) {
                $this->table(array_keys($row), [array_values($row)]);
            }
        }

        $this->line("\n✅ Debug selesai!");
    }
}
