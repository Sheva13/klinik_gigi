<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

// Models
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;
use App\Models\JadwalHarian;

class ReservasiService
{
    // Helper: Generate No Pemeriksaan (RSV-YYYYMMDDXXX)
    public function generateNoPemeriksaan()
    {
        $tanggal = Carbon::now()->format('Ymd');
        $prefix = "RSV-{$tanggal}";

        $lastReservasi = Reservasi::where('no_pemeriksaan', 'LIKE', $prefix . '%')
                                     ->whereDate('created_at', Carbon::today())
                                     ->orderBy('no_pemeriksaan', 'desc')
                                     ->first();

        $urutan = 1;
        if ($lastReservasi) {
            $lastNumber = (int) substr($lastReservasi->no_pemeriksaan, -3);
            $urutan = $lastNumber + 1;
        }

        return $prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT);
    }

    // Cek apakah jadwal tersedia
    public function cekKetersediaanJadwal($jadwal_id, $tanggal_pesan)
    {
        $jadwal = MasterJadwal::find($jadwal_id);
        if (!$jadwal) {
            throw new Exception('Jadwal tidak ditemukan');
        }

        // Validasi Hari
        $hariPilihan = Carbon::parse($tanggal_pesan)->dayOfWeekIso;
        if ($hariPilihan != $jadwal->hari) {
            throw new Exception("Jadwal tidak tersedia di hari tersebut.");
        }

        // Validasi Libur
        $cekLibur = JadwalHarian::where('kode_jadwal', $jadwal->id)
                                ->where('tanggal', $tanggal_pesan)
                                ->where('validasi', 0)->exists();
        if ($cekLibur) {
            throw new Exception('Dokter sedang libur.');
        }

        // Validasi Kuota
        $reservasiTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
            ->where('tanggal_pesan', $tanggal_pesan)
            ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'lunas'])
            ->count();

        if ($reservasiTerpakai >= $jadwal->quota) {
            throw new Exception('Maaf, kuota penuh.');
        }

        return true;
    }

    // Simpan reservasi ke database
    public function simpanReservasi($data)
    {
        return Reservasi::create([
            'no_pemeriksaan'    => $data['no_pemeriksaan'],
            'no_antrian'        => null, // Diisi oleh Webhook
            'pasien_id'         => $data['pasien_id'],
            'dokter_id'         => $data['dokter_id'],
            'jadwal_id'         => $data['jadwal_id'],
            'tanggal_pesan'     => $data['tanggal_pesan'],
            'waktu_pesan'       => $data['waktu_pesan'],
            'jam_mulai'         => $data['jam_mulai'],
            'jam_selesai'       => $data['jam_selesai'],
            'keluhan'           => $data['keluhan'],
            'biaya_reservasi'   => $data['biaya_reservasi'],
            'pembayaran_total'  => $data['pembayaran_total'],
            'status'            => $data['status'],
            'status_reservasi'  => $data['status_reservasi'],
            'metode_pembayaran' => $data['metode_pembayaran'],
            'status_pembayaran' => $data['status_pembayaran'],
            'jenis_pasien'      => $data['jenis_pasien'],
        ]);
    }

    // Ambil data jadwal dengan kuota
    public function getJadwalDenganKuota($request)
    {
        $request->validate([
            'kode_poli'         => 'required|string',
            'kode_dokter'       => 'nullable|string',
            'tanggal_reservasi' => 'nullable|date_format:Y-m-d',
        ]);

        try {
            // Auto Cancel Expired (> 60 menit)
            $batasWaktu = Carbon::now()->subMinutes(60);
            Reservasi::where('status_pembayaran', 'menunggu_pembayaran')
                ->where('created_at', '<', $batasWaktu)
                ->update([
                    'status_reservasi'  => 'batal',
                    'status_pembayaran' => 'gagal',
                    'status'            => 'Dibatalkan (Waktu Habis)'
                ]);

            $query = MasterJadwal::query();
            $kodePoli = $request->kode_poli;

            $query->where(function($q) use ($kodePoli) {
                $q->where('kode_poli', $kodePoli)
                  ->orWhereHas('dokter', function ($dq) use ($kodePoli) {
                      $dq->where('kode_poli', $kodePoli);
                  });
            });

            if ($request->filled('kode_dokter') && strtolower($request->kode_dokter) !== 'semua') {
                $query->where('kode_dokter', $request->kode_dokter);
            }

            $isDateSelected = $request->filled('tanggal_reservasi');
            $tanggalReservasi = $request->tanggal_reservasi;

            if ($isDateSelected) {
                $hariInggris = Carbon::parse($tanggalReservasi)->format('l');
                $hariMapping = [
                    'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
                    'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7,
                ];
                $query->where('hari', $hariMapping[$hariInggris]);
            }

            $jadwalList = $query->with(['dokter', 'poli'])->get();

            if ($jadwalList->isEmpty()) {
                return collect([]);
            }

            $hasil = $jadwalList->map(function ($jadwal) use ($isDateSelected, $tanggalReservasi) {
                $sisaKuota = $jadwal->quota;
                $kuotaTerpakai = 0;
                $statusJadwal = 'Tersedia';

                if ($isDateSelected) {
                    $cekLibur = JadwalHarian::where('kode_jadwal', $jadwal->id)
                                         ->where('tanggal', $tanggalReservasi)
                                         ->where('validasi', 0)
                                         ->exists();

                    if ($cekLibur) {
                        $statusJadwal = 'Libur';
                        $sisaKuota = 0;
                    } else {
                        $kuotaTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                            ->where('tanggal_pesan', $tanggalReservasi)
                            ->whereIn('status_pembayaran', [
                                'menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'lunas'
                            ])
                            ->count();

                        $sisaKuota = $jadwal->quota - $kuotaTerpakai;
                        if ($sisaKuota <= 0) $statusJadwal = 'Penuh';
                    }
                }

                $namaHari = match ($jadwal->hari) {
                    1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                    4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
                    default => '-'
                };

                return [
                    'jadwal_id'       => $jadwal->id,
                    'kode_dokter'     => $jadwal->kode_dokter,
                    'nama_dokter'     => $jadwal->dokter->nama ?? 'Dokter Umum',
                    'kode_poli'       => $jadwal->kode_poli,
                    'nama_poli'       => $jadwal->poli->nama_poli ?? '-',
                    'hari'            => $namaHari,
                    'jam_mulai'       => $jadwal->jam_mulai,
                    'jam_selesai'     => $jadwal->jam_selesai,
                    'kuota_total'     => $jadwal->quota,
                    'kuota_terpakai'  => $isDateSelected ? $kuotaTerpakai : 0,
                    'sisa_kuota'      => $sisaKuota,
                    'status_jadwal'   => $statusJadwal,
                    'tanggal_pilih'   => $isDateSelected ? $tanggalReservasi : null
                ];
            });

            return $hasil;
        } catch (Exception $e) {
            Log::error('Get Jadwal Error: ' . $e->getMessage());
            throw $e;
        }
    }
}