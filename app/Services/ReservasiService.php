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
            throw new Exception('Jadwal tidak tersedia karena dokter sedang libur.');
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

    // Mendapatkan daftar tanggal yang memiliki jadwal dokter
    public function getTanggalDenganJadwal($request)
    {
        $request->validate([
            'kode_poli' => 'nullable|string',
            'kode_dokter' => 'nullable|string',
        ]);

        $query = MasterJadwal::query();

        // Filter berdasarkan poli jika disediakan dan bukan 'semua'
        if ($request->filled('kode_poli') && strtolower($request->kode_poli) !== 'semua') {
            $kodePoli = $request->kode_poli;
            $query->where(function($q) use ($kodePoli) {
                $q->where('kode_poli', $kodePoli)
                  ->orWhereHas('dokter', function ($dq) use ($kodePoli) {
                      $dq->where('kode_poli', $kodePoli);
                  });
            });
        }

        // Filter berdasarkan dokter jika disediakan dan bukan 'semua'
        if ($request->filled('kode_dokter') && strtolower($request->kode_dokter) !== 'semua') {
            $query->where('kode_dokter', $request->kode_dokter);
        }

        $jadwalList = $query->get();

        if ($jadwalList->isEmpty()) {
            return collect([]);
        }

        $tanggalDenganJadwal = collect();

        // Ambil rentang 7 hari ke depan
        $tanggalMulai = Carbon::today();
        $tanggalAkhir = Carbon::today()->addDays(7);

        // Ambil semua jadwal harian yang cocok dengan filter dan dalam rentang waktu
        $jadwalHarians = JadwalHarian::whereIn('kode_jadwal', $jadwalList->pluck('id'))
                              ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                              ->where('validasi', '!=', 0) // Hanya yang bukan libur
                              ->get();

        // Kelompokkan berdasarkan tanggal
        $groupedByDate = $jadwalHarians->groupBy('tanggal');

        foreach ($groupedByDate as $tanggalFormatted => $jadwalHarianGroup) {
            $tanggalObj = Carbon::parse($tanggalFormatted);

            // Cek apakah ada jadwal aktif dengan kuota tersedia untuk tanggal ini
            $adaJadwalAktif = false;

            foreach ($jadwalHarianGroup as $jadwalHarian) {
                // Dapatkan master jadwal terkait
                $jadwal = $jadwalList->firstWhere('id', $jadwalHarian->kode_jadwal);

                if ($jadwal) {
                    // Cek apakah masih ada kuota tersedia
                    $kuotaTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                        ->where('tanggal_pesan', $tanggalFormatted)
                        ->whereIn('status_pembayaran', [
                            'menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'lunas'
                        ])
                        ->count();

                    if ($kuotaTerpakai < $jadwal->quota) {
                        $adaJadwalAktif = true;
                        break;
                    }
                }
            }

            if ($adaJadwalAktif) {
                $tanggalDenganJadwal->push([
                    'tanggal' => $tanggalFormatted,
                    'nama_hari' => $tanggalObj->translatedFormat('l'), // Gunakan translatedFormat untuk nama hari dalam bahasa Indonesia
                    'tanggal_indonesia' => $tanggalObj->translatedFormat('d F Y')
                ]);
            }
        }

        // Urutkan tanggal dari yang terdekat ke terjauh
        $tanggalDenganJadwal = $tanggalDenganJadwal->sortBy('tanggal')->values();

        return $tanggalDenganJadwal;
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
            'kode_poli'         => 'nullable|string',
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

            $isDateSelected = $request->filled('tanggal_reservasi');
            $tanggalReservasi = $request->tanggal_reservasi;

            // Bangun query dasar untuk MasterJadwal
            $query = MasterJadwal::query();

            // Filter berdasarkan poli jika disediakan dan bukan 'semua'
            if ($request->filled('kode_poli') && strtolower($request->kode_poli) !== 'semua') {
                $kodePoli = $request->kode_poli;
                $query->where(function($q) use ($kodePoli) {
                    $q->where('kode_poli', $kodePoli)
                      ->orWhereHas('dokter', function ($dq) use ($kodePoli) {
                          $dq->where('kode_poli', $kodePoli);
                      });
                });
            }

            // Filter berdasarkan dokter jika disediakan
            if ($request->filled('kode_dokter') && strtolower($request->kode_dokter) !== 'semua') {
                $query->where('kode_dokter', $request->kode_dokter);
            }

            $masterJadwalList = $query->with(['dokter', 'poli'])->get();

            if ($masterJadwalList->isEmpty()) {
                return collect([]);
            }

            $hasil = collect();

            // Jika tanggal dipilih, hanya cari jadwal untuk tanggal tersebut
            if ($isDateSelected) {
                foreach ($masterJadwalList as $jadwal) {
                    // Cari entri di jadwal_harian untuk tanggal yang dipilih
                    $jadwalHarian = JadwalHarian::where('kode_jadwal', $jadwal->id)
                                         ->where('tanggal', $tanggalReservasi)
                                         ->first();

                    // Jika dokter libur di tanggal tersebut, lewati
                    if ($jadwalHarian && $jadwalHarian->validasi == 0) {
                        continue;
                    }

                    // Hitung kuota terpakai
                    $kuotaTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                        ->where('tanggal_pesan', $tanggalReservasi)
                        ->whereIn('status_pembayaran', [
                            'menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'lunas'
                        ])
                        ->count();

                    $sisaKuota = $jadwal->quota - $kuotaTerpakai;
                    $statusJadwal = ($sisaKuota <= 0) ? 'Penuh' : 'Tersedia';

                    if ($jadwalHarian) {
                        $namaHari = match ($jadwal->hari) {
                            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
                            default => '-'
                        };

                        $hasil->push([
                            'jadwal_id'       => $jadwal->id,
                            'kode_dokter'     => $jadwal->kode_dokter,
                            'nama_dokter'     => $jadwal->dokter->nama ?? 'Dokter Umum',
                            'kode_poli'       => $jadwal->kode_poli,
                            'nama_poli'       => $jadwal->poli->nama_poli ?? '-',
                            'hari'            => $namaHari,
                            'jam_mulai'       => $jadwal->jam_mulai,
                            'jam_selesai'     => $jadwal->jam_selesai,
                            'kuota_total'     => $jadwal->quota,
                            'kuota_terpakai'  => $kuotaTerpakai,
                            'sisa_kuota'      => $sisaKuota,
                            'status_jadwal'   => $statusJadwal,
                            'tanggal_pilih'   => $tanggalReservasi,
                            'tanggal_jadwal_harian' => $jadwalHarian->tanggal
                        ]);
                    }
                }
            } else {
                // Jika tidak ada tanggal yang dipilih, cari semua jadwal dari jadwal_harian dalam 7 hari ke depan
                $tanggalMulai = Carbon::today();
                $tanggalAkhir = Carbon::today()->addDays(7);

                foreach ($masterJadwalList as $jadwal) {
                    // Ambil semua jadwal harian untuk jadwal ini dalam 7 hari ke depan
                    $jadwalHarians = JadwalHarian::where('kode_jadwal', $jadwal->id)
                                          ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                                          ->where('validasi', '!=', 0) // Hanya ambil yang bukan libur
                                          ->get();

                    foreach ($jadwalHarians as $jadwalHarian) {
                        // Hitung kuota terpakai untuk tanggal ini
                        $kuotaTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                            ->where('tanggal_pesan', $jadwalHarian->tanggal)
                            ->whereIn('status_pembayaran', [
                                'menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'lunas'
                            ])
                            ->count();

                        $sisaKuota = $jadwal->quota - $kuotaTerpakai;
                        $statusJadwal = ($sisaKuota <= 0) ? 'Penuh' : 'Tersedia';

                        $namaHari = match ($jadwal->hari) {
                            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
                            default => '-'
                        };

                        $hasil->push([
                            'jadwal_id'       => $jadwal->id,
                            'kode_dokter'     => $jadwal->kode_dokter,
                            'nama_dokter'     => $jadwal->dokter->nama ?? 'Dokter Umum',
                            'kode_poli'       => $jadwal->kode_poli,
                            'nama_poli'       => $jadwal->poli->nama_poli ?? '-',
                            'hari'            => $namaHari,
                            'jam_mulai'       => $jadwal->jam_mulai,
                            'jam_selesai'     => $jadwal->jam_selesai,
                            'kuota_total'     => $jadwal->quota,
                            'kuota_terpakai'  => $kuotaTerpakai,
                            'sisa_kuota'      => $sisaKuota,
                            'status_jadwal'   => $statusJadwal,
                            'tanggal_pilih'   => $jadwalHarian->tanggal,
                            'tanggal_jadwal_harian' => $jadwalHarian->tanggal
                        ]);
                    }
                }
            }

            // Urutkan hasil berdasarkan tanggal dan jam mulai
            $hasil = $hasil->sortBy([
                ['tanggal_pilih', 'asc'],
                ['jam_mulai', 'asc']
            ])->values();

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

    // Ambil daftar dokter yang tersedia pada tanggal tertentu (untuk kasus memilih poli tanpa dokter)
    public function getDokterDenganJadwal($request)
    {
        $request->validate([
            'kode_poli'         => 'required|string',
            'tanggal_reservasi' => 'required|date_format:Y-m-d',
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

            $tanggalReservasi = $request->tanggal_reservasi;

            // Query langsung dari MasterJadwal berdasarkan poli
            $query = MasterJadwal::query();

            // Filter berdasarkan poli jika bukan 'semua'
            if (strtolower($request->kode_poli) !== 'semua') {
                $query->where(function($q) use ($request) {
                    $q->where('kode_poli', $request->kode_poli)
                      ->orWhereHas('dokter', function ($dq) use ($request) {
                          $dq->where('kode_poli', $request->kode_poli);
                      });
                });
            }

            $jadwalList = $query->with(['dokter', 'poli'])->get();

            if ($jadwalList->isEmpty()) {
                return collect([]); // Tetap kembalikan collection kosong
            }

            $dokterTersedia = collect();

            foreach ($jadwalList as $jadwal) {
                // Cek apakah dokter ini libur di tanggal tersebut
                $jadwalHarian = JadwalHarian::where('kode_jadwal', $jadwal->id)
                                     ->where('tanggal', $tanggalReservasi)
                                     ->first();

                // Jika dokter libur di tanggal tersebut, lewati
                if ($jadwalHarian && $jadwalHarian->validasi == 0) {
                    continue; // Lewati jadwal ini karena dokter libur
                }

                // Cek apakah masih ada kuota tersedia
                $kuotaTerpakai = Reservasi::where('jadwal_id', $jadwal->id)
                    ->where('tanggal_pesan', $tanggalReservasi)
                    ->whereIn('status_pembayaran', [
                        'menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi', 'lunas'
                    ])
                    ->count();

                $sisaKuota = $jadwal->quota - $kuotaTerpakai;

                if ($sisaKuota > 0) {
                    // Gunakan tanggal dari jadwal_harian jika ada
                    $tanggalAktual = $jadwalHarian ? $jadwalHarian->tanggal : $tanggalReservasi;

                    $dokterTersedia->push([
                        'kode_dokter'     => $jadwal->kode_dokter,
                        'nama_dokter'     => $jadwal->dokter->nama ?? 'Dokter Umum',
                        'gelar'           => $jadwal->dokter->gelar ?? '',
                        'kode_poli'       => $jadwal->kode_poli,
                        'nama_poli'       => $jadwal->poli->nama_poli ?? '-',
                        'jadwal_id'       => $jadwal->id,
                        'jam_mulai'       => $jadwal->jam_mulai,
                        'jam_selesai'     => $jadwal->jam_selesai,
                        'sisa_kuota'      => $sisaKuota,
                        'tanggal_praktik' => $tanggalAktual,
                        'tanggal_jadwal_harian' => $tanggalAktual
                    ]);
                }
                // Jika dokter libur atau kuota penuh, tidak ada yang ditambahkan ke koleksi
            }

            // Hapus duplikasi dokter (jika dokter memiliki lebih dari satu jadwal di hari yang sama)
            $dokterTersedia = $dokterTersedia->unique('kode_dokter');

            // Konversi ke array untuk memastikan Flutter menerima list
            return $dokterTersedia->values(); // values() untuk reset key numerik
        } catch (Exception $e) {
            Log::error('Get Dokter Dengan Jadwal Error: ' . $e->getMessage());
            throw $e;
        }
    }
}