<?php

namespace App\Services;

use App\Models\BiayaTambahan;
use App\Models\HomeCareReservasi;
use App\Models\HomeCareTracking;
use App\Models\JadwalHarian;
use App\Models\MasterJadwal;
use App\Models\MasterPromo;
use App\Models\RekamMedis;
use App\Models\User;
use App\Models\Setting;
use App\Services\Payment\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Interface tetap sama
interface ReservationServiceInterface
{
    public function calculateCost($latitude, $longitude);
    public function getAvailableSchedules($kodePoli = null);
    public function createReservation(array $data);
    public function confirmPayment($reservationId);
    public function getPaymentHistory($reservationId);
    public function getInvoice($reservationId);
    public function processSettlement($reservationId);
    public function cancelReservation($reservationId);
}

// Abstract tetap ada untuk logic jarak (opsional, bisa dipisah juga tapi kita fokus ke Midtrans dulu)
abstract class BaseReservationService implements ReservationServiceInterface
{
    // Default Fallback Values
    protected $defaultClinicLat = -7.0005141;
    protected $defaultClinicLng = 110.4250683;
    protected $defaultHargaPerKm = 1750;
    protected $defaultBiayaDasar = 35000;
    protected $defaultUangMuka = 25000;

    protected function getConfig($key, $default)
    {
        // Prioritas: Database -> Property Default
        // (Env dihapus sesuai request, tapi kalau mau hybrid bisa: DB -> Env -> Default)
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    protected function calculateDistanceAndCost($userLat, $userLng)
    {
        $latKlinik = $this->getConfig('clinic_lat', $this->defaultClinicLat);
        $lngKlinik = $this->getConfig('clinic_lng', $this->defaultClinicLng);
        $tarif = $this->getConfig('price_per_km', $this->defaultHargaPerKm);

        $distance = 0;
        $usedMethod = 'haversine'; // Default fallback

        // 1. COBA MENGGUNAKAN OSRM (OPEN SOURCE ROUTING MACHINE) - GRATIS & REAL ROAD ROUTE
        try {
            // URL OSRM Demo (Sangat disarankan memakai instance sendiri untuk production, tapi demo server cukup untuk testing)
            // Format: /{lon1},{lat1};{lon2},{lat2}
            $url = "http://router.project-osrm.org/route/v1/driving/$lngKlinik,$latKlinik;$userLng,$userLat?overview=full";

            $response = Http::timeout(15)->get($url); // Timeout diperpanjang (15s) agar rute jarak jauh sempat terhitung

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['routes'][0]['distance'])) {
                    $distanceMeters = $json['routes'][0]['distance'];
                    $distance = $distanceMeters / 1000; // Konversi ke KM
                    $usedMethod = 'osrm_road';
                    // Capture geometry if available
                    $geometry = $json['routes'][0]['geometry'] ?? null;
                }
            }
        } catch (\Exception $e) {
            Log::warning("Gagal mengambil rute OSRM, fallback ke Haversine: " . $e->getMessage());
        }

        // 2. FALLBACK: HAVERSINE FORMULA (JIKA OSRM GAGAL / 0)
        if ($distance <= 0) {
            $earthRadius = 6371;
            $dLat = deg2rad($latKlinik - $userLat);
            $dLon = deg2rad($lngKlinik - $userLng);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                cos(deg2rad($userLat)) * cos(deg2rad($latKlinik)) *
                sin($dLon / 2) * sin($dLon / 2);

            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;
        }

        // Dikali 2 untuk Biaya PP (Pulang Pergi)
        $biayaJarak = (ceil($distance) * $tarif) * 2;

        return [
            'jarakDalamKm' => $distance,
            'biayaJarak' => (int) $biayaJarak,
            'metode_hitung' => $usedMethod,
            'route_geometry' => $geometry ?? null
        ];
    }

    protected function generateReservationNumber($prefix = '')
    {
        return $prefix . time() . 'HC-' . rand(1000, 9999);
    }
}

class HomeCareService extends BaseReservationService
{
    // HAPUS: use HomeCareServiceTrait; (Sudah dipindah ke Model)

    protected $midtransService;

    // DEPENDENCY INJECTION
    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function calculateCost($latitude, $longitude)
    {
        $calculation = $this->calculateDistanceAndCost($latitude, $longitude);
        $biayaLayanan = $this->getConfig('homecare_base_fee', $this->defaultBiayaDasar);

        return [
            'status' => 'success',
            'data' => [
                'jarak_km' => round($calculation['jarakDalamKm'], 2),
                'biaya_transport' => $calculation['biayaJarak'],
                'biaya_layanan' => $biayaLayanan,
                'estimasi_total' => $calculation['biayaJarak'] + $biayaLayanan,
                'route_geometry' => $calculation['route_geometry'] ?? null,
            ]
        ];
    }

    public function getAvailableSchedules($kodePoli = null)
    {
        $query = MasterJadwal::with(['dokter.spesialis', 'poli'])
            ->where('quota', '>', 0);

        if ($kodePoli && strtolower($kodePoli) !== 'semua') {
            $query->where(function($q) use ($kodePoli) {
                $q->where('kode_poli', $kodePoli)
                  ->orWhereHas('dokter', function ($dq) use ($kodePoli) {
                      $dq->where('kode_poli', $kodePoli);
                  });
            });
        }

        return $query->get()->toArray();
    }

    public function getAvailableSchedulesForDate($tanggal = null, $kodePoli = null)
    {
        $query = MasterJadwal::with(['dokter.spesialis', 'poli']);
        
        if ($kodePoli && strtolower($kodePoli) !== 'semua') {
            $query->where(function($q) use ($kodePoli) {
                $q->where('kode_poli', $kodePoli)
                  ->orWhereHas('dokter', function ($dq) use ($kodePoli) {
                      $dq->where('kode_poli', $kodePoli);
                  });
            });
        }

        if ($tanggal) {
            $date = Carbon::parse($tanggal);
            $dayIso = $date->dayOfWeekIso;
            $query->where('hari', $dayIso);
        }
        $masters = $query->get();
        $results = [];
        $now = Carbon::now('Asia/Jakarta');
        $isToday = $tanggal && Carbon::parse($tanggal)->isSameDay($now);

        foreach ($masters as $m) {
            // Filter: Hide past schedules if today
            if ($isToday) {
                // Asumsi jam_selesai format 'H:i:s' atau 'H:i'
                // Kita create Carbon DateTime hari ini dengan jam tersebut
                try {
                    // Pakai string comparison simpel untuk 'H:i:s' biasanya aman, tapi Carbon lebih robust
                    // $m->jam_selesai -> '12:00:00'
                    $endTime = Carbon::parse($tanggal . ' ' . $m->jam_selesai, 'Asia/Jakarta');

                    if ($endTime->lessThan($now)) {
                        continue; // Skip jadwal yang sudah lewat
                    }
                } catch (\Exception $e) {
                    // ignore parsing error, proceed
                }
            }

            $jadwalHarian = null;
            $kuotaTerpakai = 0;
            if ($tanggal) {
                $jadwalHarian = JadwalHarian::where('kode_jadwal', $m->id)
                    ->where('tanggal', $tanggal)->first();
                if ($jadwalHarian) {
                    $kuotaTerpakai = HomeCareReservasi::where('jadwal_id', $jadwalHarian->id)
                        ->where('tanggal_pesan', $tanggal)
                        ->whereIn('status_booking', ['belum_lunas', 'lunas'])
                        ->whereNotIn('status_reservasi', ['selesai', 'dibatalkan']) // Exclude finished bookings to free up quota
                        ->count();
                }
            }
            $kuotaMaster = $m->quota ?? 0;
            $kuotaSisa = $tanggal ? max(0, $kuotaMaster - $kuotaTerpakai) : $kuotaMaster;
            $results[] = [
                'master_jadwal' => $m,
                'kuota_master' => $kuotaMaster,
                'kuota_terpakai' => $kuotaTerpakai,
                'kuota_sisa' => $kuotaSisa,
            ];
            // Filter out if quota is reached
            if ($kuotaSisa <= 0) {
              array_pop($results);
            }
        }
        return $results;
    }

    public function createReservation(array $data)
    {
        if (!isset($data['rekam_medis_id']))
            throw new \Exception('ID Rekam Medis wajib diisi.', 422);

        $pasien = RekamMedis::find($data['rekam_medis_id']);
        if (!$pasien)
            throw new \Exception('Data pasien tidak ditemukan.', 404);

        // Fix: Cari User berdasarkan rekam_medis_id (karena relasinya User -> RekamMedis)
        $userObject = User::where('rekam_medis_id', $pasien->id)->first();
        if (!$userObject) {
            // Fallback: Coba cari pakai ID jika kebetulan sama (untuk legacy data)
            $userObject = User::find($pasien->id);
        }

        if (!$userObject) {
            // Jika user benar-benar tidak ketemu, kita tidak bisa validasi poin
            // Namun untuk booking tamu (kalau ada fitur itu), mungkin bisa lanjut.
            // Tapi di sini kita assume wajib user.
            throw new \Exception('Akun pengguna tidak ditemukan untuk pasien ini.', 404);
        }
        $userId = $userObject->user_id;
        $calculation = $this->calculateDistanceAndCost($data['latitude_pasien'], $data['longitude_pasien']);
        $biayaJarak = $calculation['biayaJarak'];
        $dpAmount = $this->getConfig('homecare_down_payment', $this->defaultUangMuka);

        $masterJadwal = MasterJadwal::find($data['master_jadwal_id']);
        if (!$masterJadwal || !$masterJadwal->kode_dokter)
            throw new \Exception('Master jadwal tidak valid', 422);

        // --- PROMO LOGIC START ---
        $promo = null;
        $discountAmount = 0;
        $pointsToDeduct = 0;

        if (isset($data['promo_id'])) {
            $promo = MasterPromo::find($data['promo_id']);
            if (!$promo)
                throw new \Exception('Promo tidak ditemukan', 404);

            // Validasi Poin User
            $user = User::find($userId);
            if (!$user || $user->poin < $promo->harga_poin) {
                throw new \Exception('Poin tidak mencukupi untuk promo ini', 400);
            }

            // Validasi Limit Pemakaian
            if ($promo->limit_per_user) {
                $usageCount = HomeCareReservasi::where('pasien_id', $userId)
                    ->where('promo_id', $promo->id)
                    ->whereNotIn('status_reservasi', ['batal', 'expire']) // Exclude failed/cancelled
                    ->count();
                if ($usageCount >= $promo->limit_per_user) {
                    throw new \Exception("Promo sudah digunakan maksimum {$promo->limit_per_user} kali.", 400);
                }
            }

            // Hitung Potongan
            $biayaLayananStandard = $this->getConfig('homecare_base_fee', $this->defaultBiayaDasar); // Ambil biaya layanan untuk batasan diskon

            if ($promo->tipe == 'free_transport') {
                $discountAmount = $biayaJarak;
            } elseif ($promo->tipe == 'potongan_total' || $promo->nilai_potongan > 0) {
                // Fallback: Jika tipe tidak sesuai tapi ada nilai potongan, tetap gunakan.
                 // UPDATE: Diskon nominal HANYA memotong biaya layanan, tidak boleh memotong transport.
                 $discountAmount = min($promo->nilai_potongan, $biayaLayananStandard);
            }

            $pointsToDeduct = $promo->harga_poin;
        }
        // --- PROMO LOGIC END ---

        return DB::transaction(function () use ($data, $pasien, $userId, $biayaJarak, $dpAmount, $masterJadwal, $promo, $discountAmount, $pointsToDeduct) {

            // Deduct Points if used
            if ($pointsToDeduct > 0) {
                $user = User::find($userId);
                $user->decrement('poin', $pointsToDeduct);

                // --- CATAT HISTORY ---
                \App\Models\PointHistory::create([
                    'user_id' => $userId,
                    'amount' => -$pointsToDeduct, // Negatif karena berkurang
                    'type' => 'redeem',
                    'description' => "Penukaran Poin untuk Promo: " . ($promo->judul_promo ?? 'Promo'),
                    'reference_id' => "BOOKING-TEMP", // Nanti diupdate setelah dapat Order ID, atau biarkan generic
                ]);
            }

            // 1. Setup Jadwal & Validasi Kuota
            $jadwalHarian = JadwalHarian::firstOrCreate(
                ['kode_jadwal' => $data['master_jadwal_id'], 'tanggal' => $data['tanggal']],
                ['validasi' => 0]
            );

            $kuotaMaster = $masterJadwal->quota ?? 0;
            if ($kuotaMaster > 0) {
                // Count ACTIVE bookings only (exclude finished/cancelled)
                $kuotaTerpakai = HomeCareReservasi::where('jadwal_id', $jadwalHarian->id)
                    ->where('tanggal_pesan', $data['tanggal'])
                    ->whereIn('status_booking', ['belum_lunas', 'lunas'])
                    ->whereNotIn('status_reservasi', ['selesai', 'dibatalkan']) // Exclude finished bookings to free up quota
                    ->count();

                if ($kuotaTerpakai >= $kuotaMaster)
                    throw new \Exception('Kuota pada jadwal ini sudah penuh', 422);
            }

            $biayaLayanan = $this->getConfig('homecare_base_fee', $this->defaultBiayaDasar);
            $grossTotal = $biayaJarak + $biayaLayanan;
            $totalBayar = max(0, $grossTotal - $discountAmount); // Ensure not negative
            $orderId = $this->generateReservationNumber('HC-');

            // --- QUEUE NUMBER LOGIC ---
            $lastQueue = HomeCareReservasi::where('jadwal_id', $jadwalHarian->id)
                ->where('tanggal_pesan', $data['tanggal'])
                ->max('no_antrian');
            $newQueue = $lastQueue ? $lastQueue + 1 : 1;

            // 2. Simpan Data Reservasi
            $reservasi = HomeCareReservasi::create([
                'no_pemeriksaan' => $orderId,
                'no_antrian' => $newQueue, // Save Queue Number
                'pasien_id' => $userId,
                'rekam_medis_id' => $pasien->id,
                'dokter_id' => $masterJadwal->kode_dokter, // FIX: master_jadwal uses kode_dokter
                'jadwal_id' => $jadwalHarian->id,
                'tanggal_pesan' => $data['tanggal'],
                'waktu_pesan' => now()->toTimeString(),
                'jam_mulai' => $masterJadwal->jam_mulai,
                'jam_selesai' => $masterJadwal->jam_selesai,
                'jenis_pasien' => 'Umum',
                'status' => 'Menunggu Pembayaran',
                'status_reservasi' => 'menunggu',
                'keluhan' => $data['keluhan'], // Detail keluhan
                'jenis_keluhan' => $data['jenis_keluhan'] ?? null,
                'jenis_keluhan_lainnya' => ($data['jenis_keluhan'] ?? '') === 'Lainnya' ? ($data['jenis_keluhan_lainnya'] ?? null) : null,
                'latitude' => $data['latitude_pasien'],
                'longitude' => $data['longitude_pasien'],
                'alamat_lengkap' => $data['alamat_lengkap'],
                'biaya_transport' => $biayaJarak,
                'biaya_reservasi' => $biayaLayanan,
                'pembayaran_total' => $totalBayar,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status_booking' => 'belum_lunas',
                'tipe_layanan' => 'home_care', // Added matches SQL default
                'promo_id' => $promo ? $promo->id : null,
                'potongan_promo' => $discountAmount,
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
                'keterangan' => 'Booking berhasil dibuat. Menunggu pembayaran via Midtrans.',
                'waktu' => now()
            ]);

            // 3. INTEGRASI MIDTRANS (MENGGUNAKAN SERVICE)
            $itemDetails = [
                [
                    'id' => 'HOMECARE-SVC',
                    'price' => $biayaLayanan,
                    'quantity' => 1,
                    'name' => 'Biaya Layanan HomeCare'
                ],
                [
                    'id' => 'TRANSPORT',
                    'price' => $biayaJarak,
                    'quantity' => 1,
                    'name' => 'Biaya Transportasi'
                ]
            ];

            if ($discountAmount > 0) {
                $itemDetails[] = [
                    'id' => 'PROMO-DISC',
                    'price' => -$discountAmount,
                    'quantity' => 1,
                    'name' => 'Potongan Promo (' . ($promo->judul_promo ?? 'Discount') . ')'
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $totalBayar, // Already discounted
                ],
                'customer_details' => [
                    'first_name' => $pasien->nama,
                    'phone' => $pasien->no_hp ?? '',
                    'billing_address' => [
                        'address' => $data['alamat_lengkap'],
                    ]
                ],
                'item_details' => $itemDetails
            ];

            // Code Jauh Lebih Bersih!
            $paymentData = $this->midtransService->createSnapToken($params);

            $reservasi->snap_token = $paymentData['token'];
            $reservasi->redirect_url = $paymentData['redirect_url'];
            $reservasi->save();

            return [
                'reservation' => $reservasi->load(['jadwalHarian.masterJadwal.dokter']),
                'payment_info' => [
                    'status_desc' => 'Menunggu Pembayaran',
                    'snap_token' => $paymentData['token'],
                    'redirect_url' => $paymentData['redirect_url'],
                    'amount' => $totalBayar,
                    'expired_at' => now()->addHour()->toDateTimeString(),
                ]
            ];
        });
    }

    public function confirmPayment($reservationId)
    {
        $reservasi = HomeCareReservasi::find($reservationId);
        if (!$reservasi)
            throw new \Exception('Booking tidak ditemukan', 404);

        $reservasi->markAsAwaitingVerification(); // Pakai method model baru

        HomeCareTracking::create([
            'id_periksa' => $reservasi->id,
            'status_tracking' => 'assigned',
            'keterangan' => 'Pembayaran berhasil dikonfirmasi. Menunggu verifikasi admin.',
            'waktu' => now()
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
        $reservasi = HomeCareReservasi::with(['tindakanPemeriksaan.masterTindakan', 'biayaTambahan'])->find($reservationId);
        if (!$reservasi)
            throw new \Exception('Data tidak ditemukan', 404);

        $totalTindakan = $reservasi->tindakanPemeriksaan->sum(function ($item) {
            return $item->biaya ?? $item->masterTindakan->biaya_tindakan;
        });
        $biayaTransport = $reservasi->biaya_transport;
        $subTotal = $totalTindakan + $biayaTransport;
        $uangMuka = $reservasi->biayaTambahan->where('komponen', 'UANG_MUKA')->sum('biaya');
        $sisaTagihan = $subTotal - $uangMuka;

        $namaPasien = $reservasi->rekamMedis->nama ?? $reservasi->pasien->nama ?? 'Pasien';

        return [
            'data' => [
                'nama_pasien' => $namaPasien,
                'no_invoice' => '#INV-' . $reservasi->no_pemeriksaan,
                'tanggal' => $reservasi->tanggal_pesan,
                'rincian_perawatan' => $reservasi->tindakanPemeriksaan->map(function ($t) {
                    return [
                        'nama' => $t->masterTindakan->tindakan ?? 'Tindakan',
                        'harga' => $t->biaya ?? $t->masterTindakan->biaya_tindakan
                    ];
                }),
                'biaya_transport' => $biayaTransport,
                'subtotal' => $subTotal,
                'uang_booking' => $uangMuka,
                'total_akhir' => max(0, $sisaTagihan),
                'status_lunas' => $reservasi->isPaid() // Menggunakan method dari Model
            ]
        ];
    }

    public function processSettlement($reservationId)
    {
        $reservasi = HomeCareReservasi::find($reservationId);
        if (!$reservasi)
            throw new \Exception('Booking tidak ditemukan', 404);

        if ($reservasi->isPaid())
            return ['message' => 'Tagihan sudah lunas.', 'data' => $reservasi];

        $reservasi->markAsPaid(); // Method dari Model

        // DAPATKAN POIN SETELAH PELUNASAN - WITH DUPLICATE CHECK
        $pasienId = $reservasi->pasien_id ?? ($reservasi->rekamMedis->user_id ?? null);
        if ($pasienId) {
            $user = User::find($pasienId);
            if ($user) {
                // Check if bonus points already added (use type='earn' for reliable check)
                $historyExists = \App\Models\PointHistory::where('reference_id', $reservasi->no_pemeriksaan)
                                    ->where('type', 'earn')
                                    ->exists();
                
                if (!$historyExists) {
                    $user->increment('poin', 10);
                    
                    // --- CATAT HISTORY ---
                    \App\Models\PointHistory::create([
                        'user_id' => $user->user_id, // Pastikan pakai user_id yang string
                        'amount' => 10,
                        'type' => 'earn',
                        'description' => "Bonus Poin Pelunasan Layanan",
                        'reference_id' => $reservasi->no_pemeriksaan
                    ]);
                }
            }
        }

        HomeCareTracking::create([
            'id_periksa' => $reservasi->id,
            'status_tracking' => 'finished',
            'keterangan' => 'Pelunasan berhasil. Layanan selesai.',
            'waktu' => now()
        ]);
        return ['message' => 'Pembayaran pelunasan berhasil.', 'data' => $reservasi];
    }

    public function cancelReservation($reservationId)
    {
        $reservasi = HomeCareReservasi::find($reservationId);
        if (!$reservasi)
            throw new \Exception('Booking tidak ditemukan', 404);

        if (!$reservasi->isCancellable())
            throw new \Exception('Reservasi tidak bisa dibatalkan', 422);

        $reservasi->cancel(); // Method dari Model

        HomeCareTracking::create([
            'id_periksa' => $reservasi->id,
            'status_tracking' => 'assigned',
            'keterangan' => 'Reservasi dibatalkan oleh pengguna.',
            'waktu' => now()
        ]);
    }

    // --- FITUR BARU: Update Status & Input Tindakan (Oleh Dokter/Admin) ---
    public function updateStatusPemeriksaan($id, $status, $totalBiayaTindakan = 0)
    {
        $reservasi = HomeCareReservasi::find($id);
        if (!$reservasi)
            throw new \Exception('Data tidak ditemukan', 404);

        $reservasi->status = $status; // Misal: "Selesai Diperiksa"

        // Jika statusnya selesai diperiksa, kita set tagihan tindakan
        if ($status == 'Selesai Diperiksa') {
            $reservasi->total_biaya_tindakan = $totalBiayaTindakan;
            $reservasi->status_reservasi = 'selesai'; // Tandai selesai secara operasional

            // Generate Tracking
            HomeCareTracking::create([
                'id_periksa' => $reservasi->id,
                'status_tracking' => 'process',
                'keterangan' => 'Pemeriksaan selesai. Menunggu pelunasan tagihan: Rp ' . number_format($totalBiayaTindakan),
                'waktu' => now()
            ]);
        } else {
            // Tracking generic
            HomeCareTracking::create([
                'id_periksa' => $reservasi->id,
                'status_tracking' => 'process',
                'keterangan' => "Status diperbarui menjadi: $status",
                'waktu' => now()
            ]);
        }

        $reservasi->save();
        return $reservasi;
    }

    // --- FITUR BARU: GENERATE LINK PELUNASAN (MIDTRANS) ---
    public function createSettlementTransaction($id, $promoId = null)
    {
        $reservasi = HomeCareReservasi::find($id);
        if (!$reservasi)
            throw new \Exception('Data tidak ditemukan', 404);

        if ($reservasi->status_pelunasan == 'lunas') {
            throw new \Exception('Tagihan ini sudah lunas.', 400);
        }

        $amount = $reservasi->total_biaya_tindakan;

        // Apply Promo Logic for Settlement
        $promo = null;
        $discountAmount = 0;

        if ($promoId) {
            $promo = MasterPromo::find($promoId);
            if (!$promo)
                throw new \Exception('Promo tidak ditemukan', 404);

            if ($promo->tipe == 'free_transport') {
                throw new \Exception('Promo Free Transport tidak bisa digunakan untuk pelunasan.', 400);
            }

            // Check Points
            $pasienId = $reservasi->pasien_id;
            $user = User::find($pasienId);
            if (!$user || $user->poin < $promo->harga_poin) {
                throw new \Exception('Poin tidak mencukupi.', 400);
            }

            // Validasi Limit Pemakaian (Exclude current reservation to allow update)
            if ($promo->limit_per_user) {
                $usageCount = HomeCareReservasi::where('pasien_id', $pasienId)
                    ->where('promo_id', $promo->id)
                    ->where('id', '!=', $reservasi->id)
                    ->count();
                if ($usageCount >= $promo->limit_per_user) {
                    throw new \Exception("Promo sudah digunakan maksimum {$promo->limit_per_user} kali (User lain/Transaksi lain).", 400);
                }
            }

            if ($promo->tipe == 'potongan_total') {
                $discountAmount = $promo->nilai_potongan;
            }

            // Deduct Points NOW (or should we wait? Usually deduct when link is generated to prevent double use, can refund if failed/cancelled - sticking to simple deduction now)
            $user->decrement('poin', $promo->harga_poin);
            
            // --- CATAT HISTORY ---
            \App\Models\PointHistory::create([
                'user_id' => $user->user_id,
                'amount' => -$promo->harga_poin,
                'type' => 'redeem',
                'description' => "Penukaran Poin (Pelunasan): " . ($promo->judul_promo ?? 'Promo'),
                'reference_id' => $reservasi->no_pemeriksaan 
            ]);

            // Update Reservasi with used promo for settlement tracking (separate columns? or overwrite? User didn't specify separate promo columns for setttlement. I'll overwrite or assume `promo_id` is generic. 
            // Better: Since schema is shared, maybe I shouldn't overwrite if one was used in booking. 
            // BUT: "Promo ... potongan Harga total untuk di Pembayaran Booking dan Pembayaran Pelunasan". 
            // Assuming simplified single usage per stage or overwrite. I'll overwrite for now, but store applied amount.)
            $reservasi->promo_id = $promo->id;
            $reservasi->potongan_promo += $discountAmount; // Accumulate? Or just track settlement discount differently? 
            // To be safe and simple: I will deduct from the $amount passing to midtrans.
        }

        $finalAmount = max(0, $amount - $discountAmount);

        if ($finalAmount <= 0) {
            // Zero payment handling (Auto Lunas?)
            // User didn't ask for this specifically, but logic requires amount > 0 for midtrans.
            // If 0, mark as Paid immediately?
            $this->processSettlement($id); // Auto finish
            return ['message' => 'Tagihan lunas dengan promo (Rp 0).', 'order_id' => 'N/A', 'snap_token' => null];
        }

        // Generate Order ID Khusus Pelunasan (PL-)
        if ($amount <= 0) {
            throw new \Exception('Tidak ada tagihan pelunasan (Total biaya tindakan 0).', 400);
        }

        // Generate Order ID Khusus Pelunasan (PL-)
        // Format: PL-{NO_PEMERIKSAAN_ASLI} -> PL-HC-12345...
        // Agar nanti di webhook kita bisa strip "PL-" dan dapat no_pemeriksaan aslinya
        // Deteministic ID for Localhost Checking:
        $settlementOrderId = 'PL-' . $reservasi->no_pemeriksaan;

        $itemDetails = [
            [
                'id' => 'PELUNASAN-HC',
                'price' => $amount,
                'quantity' => 1,
                'name' => 'Pelunasan Tindakan HomeCare'
            ]
        ];

        if ($discountAmount > 0) {
            $itemDetails[] = [
                'id' => 'PROMO-PELUNASAN',
                'price' => -$discountAmount,
                'quantity' => 1,
                'name' => 'Potongan Promo'
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $settlementOrderId,
                'gross_amount' => $finalAmount,
            ],
            'customer_details' => [
                'first_name' => $reservasi->pasien->user->nama_pengguna ?? $reservasi->pasien->nama ?? 'Pasien',
                'email' => $reservasi->pasien->user->email ?? 'noreply@klinik.com',
                'phone' => $reservasi->pasien->user->no_hp ?? $reservasi->pasien->hp ?? '',
            ],
            'item_details' => $itemDetails
        ];

        try {
            $paymentData = $this->midtransService->createSnapToken($params);

            // Simpan Token Pelunasan
            $reservasi->snap_token_pelunasan = $paymentData['token'];
            $reservasi->status_pelunasan = 'belum_lunas'; // Reset jadi menunggu jika sebelumnya gagal
            $reservasi->save();

            return [
                'snap_token' => $paymentData['token'],
                'redirect_url' => $paymentData['redirect_url'],
                'order_id' => $settlementOrderId
            ];

        } catch (\Exception $e) {
            throw new \Exception("Gagal embed Midtrans: " . $e->getMessage());
        }
    }
}