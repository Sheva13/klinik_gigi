<?php

namespace App\Services;

use App\Models\BiayaTambahan;
use App\Models\HomeCareReservasi;
use App\Models\HomeCareTracking;
use App\Models\JadwalHarian;
use App\Models\MasterJadwal;
use App\Models\RekamMedis; // Tambahkan Model RekamMedis
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service HomeCare yang dimodifikasi untuk menerima rekam_medis_id langsung
 * (Mengikuti pola ReservasiController agar lebih fleksibel)
 */
trait HomeCareServiceTrait
{
    // ... Trait method (isPaid, isPendingPayment, dll) TETAP SAMA ...
    public function isPaid(): bool { return strtolower($this->status_pembayaran) === 'lunas'; }
    public function isPendingPayment(): bool { return strtolower($this->status_pembayaran) === 'menunggu_pembayaran'; }
    public function isAwaitingVerification(): bool { return strtolower($this->status_pembayaran) === 'menunggu_verifikasi'; }
    public function isVerified(): bool { return strtolower($this->status_pembayaran) === 'terverifikasi'; }
    public function getTotal(): float { return (float) $this->pembayaran_total; }
    public function getServiceCost(): float { return (float) ($this->biaya_reservasi ?? 0); }
    public function getRemainingPayment(): float { 
        $paid = $this->biayaTambahan()->where('komponen', 'UANG_MUKA')->sum('biaya');
        return max(0, $this->pembayaran_total - $paid);
    }
    public function getDownPayment(): float {
        return (float) ($this->biayaTambahan()->where('komponen', 'UANG_MUKA')->sum('biaya') ?? 0);
    }
    public function isCancellable(): bool {
        return !$this->isPaid() && !in_array(strtolower($this->status_reservasi), ['selesai', 'dibatalkan']);
    }
    public function cancel(): void {
        $this->status_reservasi = 'dibatalkan';
        $this->status = 'Dibatalkan';
        $this->save();
    }
}

interface ReservationServiceInterface
{
    public function calculateCost($latitude, $longitude);
    public function getAvailableSchedules();
    public function createReservation(array $data);
    public function confirmPayment($reservationId);
    public function getPaymentHistory($reservationId);
    public function getInvoice($reservationId);
    public function processSettlement($reservationId);
    public function cancelReservation($reservationId);
}

abstract class BaseReservationService implements ReservationServiceInterface
{
    protected $clinicLat = -7.0005141;
    protected $clinicLng = 110.4250683;
    protected $hargaPerKm = 5000;
    protected $biayaDasar = 35000;
    protected $uangMuka = 25000;

    protected function calculateDistanceAndCost($userLat, $userLng)
    {
        $latKlinik = env('CLINIC_LAT', $this->clinicLat);
        $lngKlinik = env('CLINIC_LNG', $this->clinicLng);
        $tarif = env('HOMECARE_HARGA_PER_KM', $this->hargaPerKm);

        $earthRadius = 6371;
        $dLat = deg2rad($latKlinik - $userLat);
        $dLon = deg2rad($lngKlinik - $userLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($userLat)) * cos(deg2rad($latKlinik)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        $biayaJarak = ceil($distance) * $tarif;

        return [
            'jarakDalamKm' => $distance,
            'biayaJarak' => (int) $biayaJarak
        ];
    }

    protected function generateReservationNumber($prefix = '')
    {
        return $prefix . time() . 'HC-' . rand(1000, 9999);
    }
}

class HomeCareService extends BaseReservationService
{
    use HomeCareServiceTrait;

    public function calculateCost($latitude, $longitude)
    {
        $calculation = $this->calculateDistanceAndCost($latitude, $longitude);
        $biayaLayanan = env('HOMECARE_BIAYA_DASAR', $this->biayaDasar);

        return [
            'status' => 'success',
            'data' => [
                'jarak_km' => round($calculation['jarakDalamKm'], 2),
                'biaya_transport' => $calculation['biayaJarak'],
                'biaya_layanan' => $biayaLayanan,
                'estimasi_total' => $calculation['biayaJarak'] + $biayaLayanan
            ]
        ];
    }

    public function getAvailableSchedules()
    {
        return MasterJadwal::with(['dokter.spesialis', 'poli'])
            ->where('quota', '>', 0)
            ->get()
            ->toArray();
    }

    public function getAvailableSchedulesForDate($tanggal = null)
    {
        // ... (Kode getAvailableSchedulesForDate SAMA PERSIS dengan sebelumnya) ...
        // Agar file tidak terlalu panjang, saya persingkat bagian ini karena tidak ada perubahan logika
        // Gunakan kode getAvailableSchedulesForDate dari file sebelumnya.
        
        $query = MasterJadwal::with(['dokter.spesialis', 'poli']);
        if ($tanggal) {
            $date = Carbon::parse($tanggal);
            $dayIso = $date->dayOfWeekIso; 
            $query->where('hari', $dayIso);
        }
        $masters = $query->get();
        $results = [];
        foreach ($masters as $m) {
            $jadwalHarian = null;
            $kuotaTerpakai = 0;
            if ($tanggal) {
                $jadwalHarian = JadwalHarian::where('kode_jadwal', $m->id)
                    ->where('tanggal', $tanggal)->first();
                if ($jadwalHarian) {
                    $kuotaTerpakai = HomeCareReservasi::where('jadwal_id', $jadwalHarian->id)
                        ->where('tanggal_pesan', $tanggal)
                        ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi'])
                        ->count();
                }
            }
            $kuotaMaster = $m->quota ?? 0;
            $kuotaSisa = $tanggal ? max(0, $kuotaMaster - $kuotaTerpakai) : $kuotaMaster;
            $results[] = [
                'master_jadwal' => $m, 'kuota_master' => $kuotaMaster,
                'kuota_terpakai' => $kuotaTerpakai, 'kuota_sisa' => $kuotaSisa,
            ];
        }
        return $results;
    }

    public function createReservation(array $data)
    {
        // --- 1. MODIFIKASI: AMBIL PASIEN DARI INPUT (Bukan dari Token) ---
        // Mirip dengan ReservasiController: ambil rekam_medis_id dari input
        
        if (!isset($data['rekam_medis_id'])) {
             throw new \Exception('ID Rekam Medis wajib diisi.', 422);
        }

        $pasien = RekamMedis::find($data['rekam_medis_id']);
        if (!$pasien) {
            throw new \Exception('Data pasien tidak ditemukan.', 404);
        }

        // Kita gunakan ID dari tabel users jika relasi ada, atau ID rekam medis itu sendiri
        // Asumsi: Kita butuh User ID untuk tabel home_care_reservasi (kolom pasien_id biasanya integer)
        // Jika RekamMedis punya kolom 'user_id', gunakan itu. Jika tidak, gunakan $pasien->id
        $userId = $pasien->user_id ?? $pasien->id; 

        // -----------------------------------------------------------------

        $calculation = $this->calculateDistanceAndCost(
            $data['latitude_pasien'],
            $data['longitude_pasien']
        );
        $biayaJarak = $calculation['biayaJarak'];
        $dpAmount = env('HOMECARE_UANG_MUKA', $this->uangMuka);

        $masterJadwal = MasterJadwal::find($data['master_jadwal_id']);
        if (!$masterJadwal || !$masterJadwal->kode_dokter) {
            throw new \Exception('Master jadwal tidak valid', 422);
        }

        return DB::transaction(function () use ($data, $pasien, $userId, $biayaJarak, $dpAmount, $masterJadwal) {
            $jadwalHarian = JadwalHarian::firstOrCreate(
                [
                    'kode_jadwal' => $data['master_jadwal_id'],
                    'tanggal' => $data['tanggal'],
                ],
                ['validasi' => 0]
            );

            // Cek Kuota (Sama seperti sebelumnya)
            $kuotaMaster = $masterJadwal->quota ?? 0;
            if ($kuotaMaster > 0) {
                $kuotaTerpakai = HomeCareReservasi::where('jadwal_id', $jadwalHarian->id)
                    ->where('tanggal_pesan', $data['tanggal'])
                    ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'terverifikasi'])
                    ->count();

                if ($kuotaTerpakai >= $kuotaMaster) {
                    throw new \Exception('Kuota pada jadwal ini sudah penuh', 422);
                }
            }

            $biayaLayanan = env('HOMECARE_BIAYA_DASAR', 35000);
            
            // Simpan Reservasi
            $reservasi = HomeCareReservasi::create([
                'no_pemeriksaan' => $this->generateReservationNumber('HC-'),
                'pasien_id'      => $userId, // ID untuk relasi ke User (jika ada)
                // TAMBAHAN: Simpan juga rekam_medis_id jika kolomnya ada di tabel
                'rekam_medis_id' => $pasien->id, 
                
                'dokter_id'      => $masterJadwal->dokter_id,
                'jadwal_id'      => $jadwalHarian->id,
                'tanggal_pesan'  => $data['tanggal'],
                'waktu_pesan'    => now()->toTimeString(),
                'jam_mulai'      => $masterJadwal->jam_mulai,
                'jam_selesai'    => $masterJadwal->jam_selesai,
                'tipe_layanan'   => 'home_care',
                'jenis_pasien'   => 'Umum',
                'status'         => 'Menunggu Pembayaran',
                'status_reservasi' => 'menunggu',
                'keluhan'        => $data['keluhan'],
                'latitude'       => $data['latitude_pasien'],
                'longitude'      => $data['longitude_pasien'],
                'alamat_lengkap' => $data['alamat_lengkap'],
                'biaya_transport' => $biayaJarak,
                'biaya_reservasi' => $biayaLayanan,
                'pembayaran_total' => $biayaJarak + $biayaLayanan,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status_pembayaran' => 'menunggu_pembayaran',
            ]);

            BiayaTambahan::create([
                'id_periksa' => $reservasi->id,
                'homecare_reservasi_id' => $reservasi->id,
                'komponen' => 'UANG_MUKA',
                'biaya' => $dpAmount,
                'qty' => 1,
                'jumlah_kali' => 1,
            ]);

            HomeCareTracking::create([
                'id_periksa' => $reservasi->id,
                'status_tracking' => 'assigned',
                'keterangan' => 'Booking berhasil dibuat. Menunggu pembayaran.',
                'waktu' => now()
            ]);

            // Payment Info
            $expiredTime = now()->addHour();
            $paymentInstructions = [];

            if ($data['metode_pembayaran'] == 'transfer') {
                $paymentInstructions = [
                    'bank_name' => 'Bank Mandiri',
                    'account_number' => '137000000000 (3K Dental Care)',
                    'amount' => $reservasi->pembayaran_total,
                ];
            } else if ($data['metode_pembayaran'] == 'qris') {
                $paymentInstructions = [
                    'qris_content' => '00020101021126580016ID.CO.QRIS.WWW.01189360091433630077770303UMI51440014ID.CO.QRIS.WWW0215ID1020030040050303UMI5204541153033605802ID59133K Dental Care6008Semarang61055012362070703A0163046B68',
                    'amount' => $reservasi->pembayaran_total,
                ];
            }

            return [
                'reservation' => $reservasi->load(['jadwalHarian.masterJadwal.dokter']),
                'payment_info' => [
                    'expired_at' => $expiredTime->toDateTimeString(),
                    'instructions' => $paymentInstructions,
                    'status_desc' => 'Menunggu Pembayaran Uang Muka',
                ]
            ];
        });
    }

    // ... Method sisanya (confirmPayment, dll) SAMA PERSIS ...
    public function confirmPayment($reservationId)
    {
        $reservasi = HomeCareReservasi::find($reservationId);
        if (!$reservasi) throw new \Exception('Booking tidak ditemukan', 404);
        $reservasi->markAsAwaitingVerification();
        HomeCareTracking::create([
            'id_periksa' => $reservasi->id, 'status_tracking' => 'assigned',
            'keterangan' => 'Pembayaran DP berhasil. Menunggu konfirmasi Admin.', 'waktu' => now()
        ]);
        return $reservasi;
    }

    public function getPaymentHistory($reservationId)
    {
        $history = HomeCareTracking::where('id_periksa', $reservationId)
            ->orderBy('waktu', 'desc')->get()->toArray();
        return ['data' => $history];
    }

    public function getInvoice($reservationId)
    {
        // PERBAIKAN: Load relasi 'rekamMedis' atau 'pasien' (sesuai nama fungsi di Model HomeCareReservasi)
        // Jika Anda menamai relasinya "rekamMedis", pastikan 'with' menggunakan nama itu.
        $reservasi = HomeCareReservasi::with(['tindakanPemeriksaan.masterTindakan', 'biayaTambahan'])
            ->find($reservationId);

        if (!$reservasi) throw new \Exception('Data tidak ditemukan', 404);

        $totalTindakan = $reservasi->tindakanPemeriksaan->sum(function ($item) {
            return $item->biaya ?? $item->masterTindakan->biaya_tindakan;
        });
        $biayaTransport = $reservasi->biaya_transport;
        $subTotal = $totalTindakan + $biayaTransport;
        $uangMuka = $reservasi->biayaTambahan->where('komponen', 'UANG_MUKA')->sum('biaya');
        $sisaTagihan = $subTotal - $uangMuka;

        // AMBIL NAMA PASIEN DINAMIS
        // Coba ambil dari relasi 'rekamMedis' dulu, jika null coba 'pasien', jika null pakai fallback string.
        // Pastikan model HomeCareReservasi memiliki method: public function rekamMedis() { return $this->belongsTo(RekamMedis::class, 'rekam_medis_id'); }
        $namaPasien = $reservasi->rekamMedis->nama ?? $reservasi->pasien->nama ?? 'Pasien (Nama Tidak Ditemukan)';

        return [
            'data' => [
                'nama_pasien' => $namaPasien, 
                'no_invoice' => '#INV-' . $reservasi->no_pemeriksaan,
                'tanggal' => $reservasi->tanggal_pesan,
                'rincian_perawatan' => $reservasi->tindakanPemeriksaan->map(function ($t) {
                    return [
                        'nama' => $t->masterTindakan->tindakan ?? 'Tindakan Medis',
                        'harga' => $t->biaya ?? $t->masterTindakan->biaya_tindakan
                    ];
                }),
                'biaya_transport' => $biayaTransport,
                'subtotal' => $subTotal,
                'uang_booking' => $uangMuka,
                'total_akhir' => max(0, $sisaTagihan),
                'status_lunas' => $reservasi->isPaid()
            ]
        ];
    }

    public function processSettlement($reservationId)
    {
        $reservasi = HomeCareReservasi::find($reservationId);
        if (!$reservasi) throw new \Exception('Booking tidak ditemukan', 404);
        if ($reservasi->isPaid()) return ['message' => 'Tagihan sudah lunas.', 'data' => $reservasi];
        $reservasi->markAsPaid();
        HomeCareTracking::create([
            'id_periksa' => $reservasi->id, 'status_tracking' => 'finished',
            'keterangan' => 'Pelunasan berhasil. Layanan selesai.', 'waktu' => now()
        ]);
        return ['message' => 'Pembayaran pelunasan berhasil.', 'data' => $reservasi];
    }

    public function cancelReservation($reservationId)
    {
        $reservasi = HomeCareReservasi::find($reservationId);
        if (!$reservasi) throw new \Exception('Booking tidak ditemukan', 404);
        if (!$reservasi->isCancellable()) throw new \Exception('Reservasi tidak bisa dibatalkan', 422);
        $reservasi->cancel();
        HomeCareTracking::create([
            'id_periksa' => $reservasi->id, 'status_tracking' => 'assigned',
            'keterangan' => 'Reservasi dibatalkan oleh pengguna.', 'waktu' => now()
        ]);
    }
}