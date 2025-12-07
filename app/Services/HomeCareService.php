<?php

namespace App\Services;

use App\Models\BiayaTambahan;
use App\Models\HomeCareReservasi;
use App\Models\HomeCareTracking;
use App\Models\JadwalHarian;
use App\Models\MasterJadwal;
use App\Models\RekamMedis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Midtrans\Config;
use Midtrans\Snap;

trait HomeCareServiceTrait
{
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
    // ... PROPERTI DAN METHOD BaseReservationService TETAP SAMA ...
    protected $clinicLat = -7.0005141;
    protected $clinicLng = 110.4250683;
    protected $hargaPerKm = 5000;
    protected $biayaDasar = 35000;
    protected $uangMuka = 25000;

    protected function calculateDistanceAndCost($userLat, $userLng)
    {
        // ... Logika hitung jarak tetap sama ...
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

    // --- HELPER MIDTRANS ---
    protected function configureMidtrans()
    {
        // Load dari env() langsung untuk menghindari cache config issue
        $serverKey = env('MIDTRANS_SERVER_KEY') ?? config('services.midtrans.server_key');
        $clientKey = env('MIDTRANS_CLIENT_KEY') ?? config('services.midtrans.client_key');
        $isProduction = env('MIDTRANS_IS_PRODUCTION', false) ?? config('services.midtrans.is_production');
        
        Config::$serverKey = $serverKey;
        Config::$clientKey = $clientKey;
        Config::$isProduction = $isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;
        
        Log::info("✅ Midtrans Config Set");
        Log::info("  ServerKey: " . (substr($serverKey ?? '', 0, 15) . "..."));
        Log::info("  ClientKey: " . (substr($clientKey ?? '', 0, 15) . "..."));
        Log::info("  IsProduction: " . ($isProduction ? 'true' : 'false'));
        Log::info("  Full ServerKey: " . $serverKey);
        Log::info("  Full ClientKey: " . $clientKey);
        
        // Extract Merchant ID dari keys (format: SB-Mid-server-XXXXX atau SB-Mid-client-XXXXX)
        $merchantIdFromServer = substr($serverKey ?? '', 0, 20);
        $merchantIdFromClient = substr($clientKey ?? '', 0, 20);
        Log::info("  Merchant Prefix (Server): " . $merchantIdFromServer);
        Log::info("  Merchant Prefix (Client): " . $merchantIdFromClient);
    }
}

class HomeCareService extends BaseReservationService
{
    use HomeCareServiceTrait;

    // ... Method calculateCost, getAvailableSchedules, getAvailableSchedulesForDate TETAP SAMA ...
    
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
        // Kode SAMA PERSIS dengan file asli Anda
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
        if (!isset($data['rekam_medis_id'])) {
             throw new \Exception('ID Rekam Medis wajib diisi.', 422);
        }

        $pasien = RekamMedis::find($data['rekam_medis_id']);
        if (!$pasien) {
            throw new \Exception('Data pasien tidak ditemukan.', 404);
        }

        $userId = $pasien->user_id ?? $pasien->id; 

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
            // 1. Buat Jadwal Harian
            $jadwalHarian = JadwalHarian::firstOrCreate(
                ['kode_jadwal' => $data['master_jadwal_id'], 'tanggal' => $data['tanggal']],
                ['validasi' => 0]
            );

            // 2. Cek Kuota
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
            $totalBayar   = $biayaJarak + $biayaLayanan;
            $orderId      = $this->generateReservationNumber('HC-');
            
            // 3. Simpan Data Reservasi
            $reservasi = HomeCareReservasi::create([
                'no_pemeriksaan' => $orderId,
                'pasien_id'      => $userId,
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
                'pembayaran_total' => $totalBayar,
                'metode_pembayaran' => $data['metode_pembayaran'], // 'transfer' atau 'qris' tidak terlalu masalah, Midtrans handle semua
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
                'keterangan' => 'Booking berhasil dibuat. Menunggu pembayaran via Midtrans.',
                'waktu' => now()
            ]);

            // --- 4. INTEGRASI MIDTRANS ---
            $this->configureMidtrans();

            // Tentukan jumlah yang harus dibayar ke Midtrans
            // OPSI A: Bayar Full ($totalBayar)
            // OPSI B: Bayar DP saja ($dpAmount) -> Ganti variable di bawah jika ingin DP saja
            $amountToPay = $totalBayar; 

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId, 
                    'gross_amount' => $amountToPay,
                ],
                'customer_details' => [
                    'first_name' => $pasien->nama,
                    'phone' => $pasien->no_hp ?? '', 
                    'billing_address' => [
                        'address' => $data['alamat_lengkap'],
                    ]
                ],
                'item_details' => [
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
                ]
            ];

            // Dapatkan Snap Token & Redirect URL
            try {
                Log::info("🔵 Attempting Midtrans createTransaction with params: " . json_encode($params));
                
                // Cukup panggil API satu kali
                $transaction = Snap::createTransaction($params);
                
                // DEBUG LOG
                Log::info("✅ Midtrans createTransaction SUCCESS");
                Log::info("Transaction Response: " . json_encode($transaction));
                
                // Ambil token dan url dari response object
                $snapToken = $transaction->token ?? null;
                $redirectUrl = $transaction->redirect_url ?? null;
                
                Log::info("Snap Token: $snapToken");
                Log::info("Redirect URL: $redirectUrl");
                
                // Simpan token dan redirect_url ke database
                $reservasi->snap_token = $snapToken;
                $reservasi->redirect_url = $redirectUrl;
                $reservasi->save();

            } catch (\Throwable $e) {
                Log::error("Error saat save snap_token ke database: " . $e->getMessage());
                Log::error("Error Code: " . $e->getCode());
                Log::error("Error Class: " . get_class($e));
                // Continue anyway - kita tetap return redirect_url ke Flutter
                // Jangan set $snapToken dan $redirectUrl ke null
            }

            return [
                'reservation' => $reservasi->load(['jadwalHarian.masterJadwal.dokter']),
                'payment_info' => [
                    'status_desc' => 'Menunggu Pembayaran',
                    'snap_token' => $snapToken,        // Token untuk Frontend (Flutter Mobile SDK)
                    'redirect_url' => $redirectUrl,    // URL untuk Frontend (WebView) - PENTING!
                    'amount' => $amountToPay,
                    'expired_at' => now()->addHour()->toDateTimeString(),
                ]
            ];
        });
    }

    public function confirmPayment($reservationId)
    {
        $reservasi = HomeCareReservasi::find($reservationId);
        if (!$reservasi) throw new \Exception('Booking tidak ditemukan', 404);
        $reservasi->markAsAwaitingVerification();
        HomeCareTracking::create([
            'id_periksa' => $reservasi->id, 'status_tracking' => 'assigned',
            'keterangan' => 'Pembayaran berhasil dikonfirmasi. Menunggu verifikasi admin.', 'waktu' => now()
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
        if (!$reservasi) throw new \Exception('Data tidak ditemukan', 404);

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

    public function handleMidtransCallback(array $payload)
    {
        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];
        $transactionStatus = $payload['transaction_status'];
        
        // 1. Validasi Signature Key (Keamanan)
        $serverKey = config('services.midtrans.server_key');
        $inputSignature = $payload['signature_key'];
        $mySignature = hash("sha512", $orderId . $statusCode . $grossAmount . $serverKey);

        if ($inputSignature !== $mySignature) {
            throw new \Exception("Invalid Signature Key");
        }

        // 2. Cari Data Reservasi
        // Asumsi order_id midtrans = no_pemeriksaan di database
        $reservasi = HomeCareReservasi::where('no_pemeriksaan', $orderId)->first();
        if (!$reservasi) {
            throw new \Exception("Order ID not found: " . $orderId);
        }

        // 3. Update Status Berdasarkan Respon Midtrans
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $reservasi->status_pembayaran = 'lunas'; // Atau 'terverifikasi' sesuai flow Anda
            $reservasi->status = 'Menunggu Dokter'; // Update status text UI
            
            // Catat di tracking
            HomeCareTracking::create([
                'id_periksa' => $reservasi->id,
                'status_tracking' => 'assigned', // sesuaikan enum
                'keterangan' => 'Pembayaran lunas via Midtrans.',
                'waktu' => now()
            ]);
            
        } else if ($transactionStatus == 'expire' || $transactionStatus == 'cancel' || $transactionStatus == 'deny') {
            $reservasi->status_pembayaran = 'gagal'; // atau dibatalkan
            $reservasi->status_reservasi = 'dibatalkan';
            $reservasi->status = 'Dibatalkan';
        } else if ($transactionStatus == 'pending') {
            $reservasi->status_pembayaran = 'menunggu_pembayaran';
        }

        $reservasi->save();
    }
}