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

        // Validasi Hari - Convert ke ISO weekday (1=Mon, 7=Sun)
        $hariPilihan = (int) Carbon::parse($tanggal_pesan)->dayOfWeekIso;
        $hariJadwal = (int) $jadwal->hari;
        
        // Log untuk debugging
        Log::info("Validasi Jadwal: ", [
            'tanggal_pesan' => $tanggal_pesan,
            'jadwal_id' => $jadwal_id,
            'hari_pilihan_iso' => $hariPilihan,
            'hari_jadwal_raw' => $jadwal->hari,
            'hari_jadwal_normalized' => $this->normalizeHari($jadwal->hari),
        ]);
        
        // Normalize hari jadwal untuk handle berbagai format
        $hariJadwalNormalized = $this->normalizeHari($jadwal->hari);
        
        if ($hariPilihan != $hariJadwalNormalized) {
            throw new Exception("Jadwal tidak tersedia di hari tersebut. (Tanggal: $tanggal_pesan, Hari pilihan: $hariPilihan, Jadwal hari: $hariJadwalNormalized)");
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

    /**
     * ✅ HELPER: Normalize berbagai format hari ke ISO Weekday (1-7)
     * 
     * Converts:
     * - 0-6 (Sunday-Saturday) → 1-7 (Monday-Sunday)
     * - 1-7 (Monday-Sunday ISO) → 1-7 (unchanged)
     * - String nama hari (ID/EN) → 1-7
     * 
     * @param mixed $hariValue Integer atau String
     * @return int ISO Weekday (1=Monday, 7=Sunday)
     */
    private function normalizeHari($hariValue)
    {
        // Jika numeric
        if (is_numeric($hariValue)) {
            $hari = (int) $hariValue;
            
            // Format: 0-6 (Sunday=0, Monday=1, ..., Saturday=6)
            // Convert to ISO (Monday=1, ..., Sunday=7)
            if ($hari === 0) return 7; // Sunday -> 7
            if ($hari >= 1 && $hari <= 6) return $hari; // Mon-Sat unchanged
            if ($hari >= 1 && $hari <= 7) return $hari; // Already ISO format
        }
        
        // Jika string nama hari
        if (is_string($hariValue)) {
            $hariNama = strtolower(trim($hariValue));
            
            // Mapping: String hari → ISO Weekday (1-7)
            $mapping = [
                // Indonesia
                'senin' => 1,
                'selasa' => 2,
                'rabu' => 3,
                'kamis' => 4,
                'jumat' => 5,
                'sabtu' => 6,
                'minggu' => 7,
                
                // English
                'monday' => 1,
                'tuesday' => 2,
                'wednesday' => 3,
                'thursday' => 4,
                'friday' => 5,
                'saturday' => 6,
                'sunday' => 7,
                
                // Abbreviations
                'sen' => 1, 'sel' => 2, 'rab' => 3, 'kam' => 4,
                'jum' => 5, 'sab' => 6, 'min' => 7,
                'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4,
                'fri' => 5, 'sat' => 6, 'sun' => 7,
            ];
            
            return $mapping[$hariNama] ?? 1;
        }
        
        return 1; // Default: Monday
    }
}