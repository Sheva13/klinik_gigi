<?php

namespace App\Services;

use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterJadwal;
use App\Models\DataPasien;
use App\Models\TransaksiBayar;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AdminReservasiService
{
    // Logika Simpan Reservasi Baru
    public function handleCreateManual($data)
    {
        // 1. Logic Pasien (Cari atau Buat Baru)
        $rekam_medis_id = $data['pasien_id_exist'] ?? null;
        $rmString = '';

        if (!$rekam_medis_id) {
            $last_rm = RekamMedis::latest('id')->first();
            $new_rm_num = $last_rm ? (int) substr($last_rm->rekam_medis, 2) + 1 : 1;
            $rekam_medis_no = 'RM' . str_pad($new_rm_num, 3, '0', STR_PAD_LEFT);

            $pasien = RekamMedis::create([
                'rekam_medis'   => $rekam_medis_no,
                'nama'          => $data['nama_lengkap'],
                'tgl_lahir'     => $data['ttl'] ?? null,
                'alamat'        => $data['alamat'] ?? null,
                'no_hp'         => $data['no_hp'] ?? null,
                'jenis_pasien'  => $data['jenis_pasien'],
            ]);
            $rmString = $pasien->rekam_medis;
        } else {
            $pasien = RekamMedis::find($rekam_medis_id);
            $rmString = $pasien ? $pasien->rekam_medis : '';
        }

        // 2. Logic Jadwal
        $tanggal_waktu_pesan = Carbon::parse($data['tanggal_janji'] . ' ' . $data['waktu_janji']);
        $day_of_week = $tanggal_waktu_pesan->dayOfWeekIso;
        
        $master = MasterJadwal::where('kode_dokter', $data['dokter'])
            ->where('hari', $day_of_week)
            ->where('jam_mulai', $data['waktu_janji'])
            ->first();
        
        $jadwal_id = $master ? $master->id : null;
        if (!$jadwal_id) throw new Exception("Jadwal Dokter atau jam praktek tidak ditemukan.");

        // 3. Simpan Reservasi
        $reservasi = Reservasi::create([
            'pasien_id'         => $rmString,
            'dokter_id'         => $data['dokter'],
            'jadwal_id'         => $jadwal_id,
            'tanggal_pesan'     => $data['tanggal_janji'],
            'waktu_pesan'       => $tanggal_waktu_pesan->format('H:i:s'),
            'jam_mulai'         => $data['waktu_janji'],
            'jam_selesai'       => Carbon::parse($data['waktu_janji'])->addMinutes(30)->format('H:i:s'),
            'keluhan'           => $data['keluhan'] ?? null,
            'status_pembayaran' => $data['status_bayar'] ?? 'menunggu_verifikasi',
            'metode_pembayaran' => $data['metode_bayar'] ?? 'Manual',
            'pembayaran_total'  => $data['total_biaya'] ?? 0,
            'status_reservasi'  => 'menunggu',
            'no_pemeriksaan'    => $this->generateNoPemeriksaan(),
            'no_antrian'        => null,
            'jenis_pasien'      => $data['jenis_pasien'],
        ]);

        // 4. Cek Auto Masuk Antrian (Jika Lunas)
        if (in_array($reservasi->status_pembayaran, ['lunas', 'terverifikasi'])) {
            $reservasi->status_pembayaran = 'terverifikasi';
            $reservasi->save();
            $this->processQueueLogic($reservasi, $rmString, $jadwal_id);
        }

        return $reservasi;
    }

    // Logika Update Reservasi
    public function handleUpdate($id, $data)
    {
        $reservasi = Reservasi::with('rekamMedis')->findOrFail($id);

        // Update basic fields
        if (isset($data['dokter_id'])) $reservasi->dokter_id = $data['dokter_id'];
        if (isset($data['tanggal_pesan'])) $reservasi->tanggal_pesan = $data['tanggal_pesan'];
        if (isset($data['keluhan'])) $reservasi->keluhan = $data['keluhan'];

        if (isset($data['jam_mulai'])) {
            $reservasi->jam_mulai = $data['jam_mulai'];
            $reservasi->jam_selesai = Carbon::parse($data['jam_mulai'])->addMinutes(30)->format('H:i:s');
        }

        if (isset($data['status_reservasi'])) {
            $reservasi->status_reservasi = $data['status_reservasi'];
        }

        // Update keuangan
        if (isset($data['metode_pembayaran'])) {
            $reservasi->metode_pembayaran = $data['metode_pembayaran'];
        }

        // Logic Pembayaran & Antrian
        if (isset($data['status_pembayaran']) && $reservasi->status_pembayaran != $data['status_pembayaran']) {
            $reservasi->status_pembayaran = $data['status_pembayaran'];

            if (in_array($data['status_pembayaran'], ['terverifikasi', 'lunas'])) {
                if($reservasi->status_reservasi == 'menunggu_pembayaran') {
                     $reservasi->status_reservasi = 'menunggu';
                }

                $rmString = $reservasi->rekamMedis ? $reservasi->rekamMedis->rekam_medis : $reservasi->pasien_id;
                $this->processQueueLogic($reservasi, $rmString, $reservasi->jadwal_id);
            }
        }

        $reservasi->save();
        return $reservasi;
    }

    // Logika Masuk Antrian (Logic kompleks dipisah disini)
    public function processQueueLogic($reservasi, $rmString, $jadwalId)
    {
        // 1. GENERATE NO ANTRIAN
        if (!$reservasi->no_antrian || $reservasi->no_antrian == '-') {
            $maxAntrian = DataPasien::where('id_jadwal', $jadwalId)->whereDate('created_at', Carbon::today())->max('no_antri');
            $urutanBaru = $maxAntrian ? ($maxAntrian + 1) : 1;

            $prefix = match($reservasi->jenis_pasien) { 'BPJS' => 'B', 'Asuransi' => 'A', default => 'U' };
            $reservasi->no_antrian = $prefix . '-' . str_pad($urutanBaru, 3, '0', STR_PAD_LEFT);
            $reservasi->save();
        } else {
            $parts = explode('-', $reservasi->no_antrian);
            $urutanBaru = (count($parts) > 1) ? (int) end($parts) : 1;
        }

        // 2. INSERT DATA PASIEN (Antrian Hari Ini)
        $cekAntrian = DataPasien::where('rekam_medis', $rmString)->where('id_jadwal', $jadwalId)->whereDate('created_at', Carbon::today())->first();
        $idPeriksa = null;

        if (!$cekAntrian) {
            $dp = DataPasien::create([
                'id_jadwal' => $jadwalId,
                'rekam_medis' => $rmString,
                'no_antri' => $urutanBaru,
                'status' => 1, 'pasien_baru' => 0, 'rujukan' => 0, 'biaya_admin' => 0,
                'keluhan' => $reservasi->keluhan,
                'tanggal_periksa' => $reservasi->tanggal_pesan
            ]);
            $idPeriksa = $dp->id;
        } else {
            $idPeriksa = $cekAntrian->id;
        }

        // 3. INSERT TRANSAKSI BAYAR
        $cekTrx = TransaksiBayar::where('id_periksa', $idPeriksa)->first();
        if (!$cekTrx && $idPeriksa) {
            TransaksiBayar::create([
                'id_periksa' => $idPeriksa,
                'ambil_obat' => 0, 'total_tindakan' => 0, 'total_obat' => 0, 'total_penunjang' => 0,
                'total_tambahan' => 0, 'total_bayar' => $reservasi->pembayaran_total,
                'waktu' => Carbon::now(), 'diskon' => 0, 'biaya_admin' => 0, 'pasien_baru' => 0,
            ]);
        }
    }

    private function generateNoPemeriksaan()
    {
        $tanggal = Carbon::now()->format('Ymd');
        $prefix = "RSV-{$tanggal}";
        $lastReservasi = Reservasi::where('no_pemeriksaan', 'LIKE', $prefix . '%')
            ->whereDate('created_at', Carbon::today())
            ->orderBy('no_pemeriksaan', 'desc')->first();

        $urutan = $lastReservasi ? ((int) substr($lastReservasi->no_pemeriksaan, -3) + 1) : 1;
        return $prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT);
    }
}