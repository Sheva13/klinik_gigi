<?php

namespace App\Http\Controllers;

use App\Models\BiayaTambahan;
use App\Models\Reservasi; // GANTI DataPasien JADI Reservasi
use App\Models\HomeCareTracking;
use App\Models\JadwalHarian;
use App\Models\MasterJadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeCareController extends Controller
{
    // Konfigurasi Hardcode (Agar aman jika env bermasalah)
    private $clinicLat = -7.0005141; 
    private $clinicLng = 110.4250683;
    private $hargaPerKm = 5000;
    private $biayaDasar = 35000;
    private $uangMuka = 25000;
    /**
     * Rumus Haversine (Private Helper)
     */
    private function calculateDistanceAndCost($userLat, $userLng)
    {   
        $latKlinik = env('CLINIC_LAT', $this->clinicLat);
        $lngKlinik = env('CLINIC_LNG', $this->clinicLng);
        $tarif = env('HOMECARE_HARGA_PER_KM', $this->hargaPerKm);

        $earthRadius = 6371; // km
        $dLat = deg2rad($latKlinik - $userLat);
        $dLon = deg2rad($lngKlinik - $userLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($userLat)) * cos(deg2rad($latKlinik)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        // Pembulatan jarak ke atas (misal 2.1 km jadi 3 km)
        $biayaJarak = ceil($distance) * $tarif;

        return [
            'jarakDalamKm' => $distance,
            'biayaJarak' => (int) $biayaJarak
        ];
    }

    // 1. API untuk Cek Ongkir (Dipanggil saat Input Lokasi di Flutter)
    public function calculateCost(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $calculation = $this->calculateDistanceAndCost(
            $request->latitude,
            $request->longitude
        );
        
        $biayaLayanan = env('HOMECARE_BIAYA_DASAR', $this->biayaDasar); 

        return response()->json([
            'status' => 'success',
            'data' => [
                'jarak_km' => round($calculation['jarakDalamKm'], 2),
                'biaya_transport' => $calculation['biayaJarak'],
                'biaya_layanan' => $biayaLayanan,
                'estimasi_total' => $calculation['biayaJarak'] + $biayaLayanan
            ]
        ]);
    }

    // 2. API Get Jadwal (Untuk Halaman Pilih Jadwal)
    public function getMasterJadwal()
    {
        $jadwal = MasterJadwal::with(['dokter.spesialis', 'poli'])
                    ->where('quota', '>', 0)
                    ->get();

        return response()->json(['data' => $jadwal]);
    }

    // 3. API Booking (Inti Transaksi)
    public function storeBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'master_jadwal_id' => 'required|exists:master_jadwal,id',
            'tanggal' => 'required|date_format:Y-m-d',
            'keluhan' => 'required|string|max:500',
            'latitude_pasien' => 'required|numeric',
            'longitude_pasien' => 'required|numeric',
            'alamat_lengkap' => 'required|string', 
            'metode_pembayaran' => 'required|in:transfer,qris',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = Auth::user();
        // Pastikan user login punya data rekam medis (Profile Pasien)
        // Jika Anda menggunakan Sanctum, $user otomatis terisi
        if (!$user || !$user->id) {
             return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 1. Hitung ulang biaya di server (Security: Jangan percaya input harga dari Frontend)
        $calculation = $this->calculateDistanceAndCost(
            $request->latitude_pasien,
            $request->longitude_pasien
        );
        $biayaJarak = $calculation['biayaJarak'];
        $dpAmount = env('HOMECARE_UANG_MUKA', $this->uangMuka);

        return DB::transaction(function () use ($request, $user, $dpAmount, $biayaJarak) {
            
            // A. Kelola Jadwal Harian (Cek ketersediaan atau buat baru)
            $jadwalHarian = JadwalHarian::firstOrCreate(
                [
                    'kode_jadwal' => $request->master_jadwal_id,
                    'tanggal' => $request->tanggal,
                ],
                ['validasi' => 0] // Default value jika baru dibuat (0 = belum validasi)
            );

            // B. Validasi bahwa user memiliki data yang diperlukan
            if (!$user->id) {
                throw new \Exception('User tidak memiliki ID yang valid');
            }

            // Ambil data Master Jadwal untuk mendapatkan dokter_id dan detail jadwal
            $masterJadwal = MasterJadwal::find($request->master_jadwal_id);
            if (!$masterJadwal) {
                throw new \Exception('Master jadwal tidak ditemukan');
            }

            // B. Simpan ke Tabel RESERVASI (Bukan DataPasien)
            // Gunakan field sesuai dengan struktur tabel reservasi
            $reservasi = Reservasi::create([
                'no_pemeriksaan' => 'HC-' . time() . '-' . rand(1000, 9999), // Generate booking reference
                'pasien_id' => $user->id, // Relasi ke pasien (dari auth user)
                'dokter_id' => $masterJadwal->dokter_id, // Ambil dari master jadwal - INI YANG KITA LUPA SEBELUMNYA!
                'jadwal_id' => $jadwalHarian->id, // Relasi ke jadwal harian
                'tanggal_pesan' => $request->tanggal, // Tanggal kunjungan dari request
                'waktu_pesan' => now()->toTimeString(), // Waktu booking dibuat
                'jam_mulai' => $masterJadwal->jam_mulai, // Ambil dari master jadwal
                'jam_selesai' => $masterJadwal->jam_selesai, // Ambil dari master jadwal
                'tipe_layanan' => 'home_care',
                'jenis_pasien' => 'Umum', // Default jenis pasien
                'status' => 0, // 0 = Menunggu Pembayaran DP
                'status_reservasi' => 'menunggu_pembayaran', // Default status
                'keluhan' => $request->keluhan,
                'latitude' => $request->latitude_pasien,
                'longitude' => $request->longitude_pasien,
                'alamat_lengkap' => $request->alamat_lengkap,
                'biaya_transport' => $biayaJarak,
                'biaya_reservasi' => env('HOMECARE_BIAYA_DASAR', 100000), // Biaya dasar home care
                'pembayaran_total' => $biayaJarak + env('HOMECARE_BIAYA_DASAR', 100000), // Total pembayaran
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'belum_dibayar', // Default status pembayaran
            ]);

            // C. Simpan Rincian Biaya
            BiayaTambahan::create([
                'id_periksa' => $reservasi->id, // Relasi ke reservasi
                'komponen' => 'UANG_MUKA',
                'biaya' => $dpAmount,
            ]);

            BiayaTambahan::create([
                'id_periksa' => $reservasi->id,
                'komponen' => 'BIAYA_JARAK',
                'biaya' => $biayaJarak,
            ]);

            // D. Mulai Tracking
            HomeCareTracking::create([
                'id_periksa' => $reservasi->id,
                'status_tracking' => 'Booking dibuat. Menunggu pembayaran DP.',
                'timestamp' => now()
            ]);

            return response()->json([
                'message' => 'Booking berhasil. Silakan lakukan pembayaran DP.',
                'data' => $reservasi->load(['jadwalHarian.masterJadwal.dokter'])
            ], 201);
        });
    }

    // 4. Konfirmasi Pembayaran
    public function confirmPayment(Request $request, $id)
    {
        // Cari di Reservasi, bukan DataPasien
        $reservasi = Reservasi::find($id);

        if (!$reservasi) {
             return response()->json(['error' => 'Booking tidak ditemukan.'], 404);
        }

        // Validasi Pemilik (Opsional, tergantung kebutuhan)
        if (Auth::id() != $reservasi->id_pasien) {
             return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        // Ubah Status
        $reservasi->status = 1; // 1 = DP Lunas / Menunggu Konfirmasi Admin
        $reservasi->save();

        // Log Tracking
        HomeCareTracking::create([
            'id_periksa' => $reservasi->id,
            'status_tracking' => 'Pembayaran DP berhasil. Menunggu konfirmasi Admin.',
            'timestamp' => now()
        ]);

        return response()->json(['message' => 'Pembayaran berhasil.', 'data' => $reservasi]);
    }

    // 5. History Tracking
    public function getTrackingHistory($id)
    {
        $history = HomeCareTracking::where('id_periksa', $id)
                                   ->orderBy('timestamp', 'desc')
                                   ->get();
        
        return response()->json(['data' => $history]);
    }
    public function getInvoice($id)
{
    $reservasi = Reservasi::with(['tindakanPemeriksaan.masterTindakan', 'biayaTambahan', 'pasien'])->find($id);
    if (!$reservasi) return response()->json(['error' => 'Data tidak ditemukan'], 404);

    // 1. Hitung Total Tindakan (Scaling + Tambal + dll)
    $totalTindakan = $reservasi->tindakanPemeriksaan->sum(function($item) {
        // Ambil harga dari tabel history jika ada, kalau null ambil dari master (fallback)
        return $item->biaya ?? $item->masterTindakan->biaya_tindakan;
    });

    // 2. Ambil Biaya Transport & Layanan (Disimpan di reservasi atau biaya tambahan)
    // Asumsi: biaya_transport sudah tersimpan di kolom reservasi saat booking
    $biayaTransport = $reservasi->biaya_transport; 

    // 3. Hitung Subtotal
    $subTotal = $totalTindakan + $biayaTransport;

    // 4. Cek Uang Muka (DP) yang sudah dibayar
    $uangMuka = $reservasi->biayaTambahan
                ->where('komponen', 'UANG_MUKA') // Sesuaikan string ini dengan saat storeBooking
                ->sum('biaya');

    // 5. Total Akhir yang harus dilunasi
    $sisaTagihan = $subTotal - $uangMuka;

    // Struktur Data untuk UI Flutter (Sesuai Desain "Rincian Tagihan")
    $dataInvoice = [
        'nama_pasien' => $reservasi->pasien->nama ?? 'Pasien',
        'no_invoice' => '#INV-' . $reservasi->no_pemeriksaan,
        'tanggal' => $reservasi->tanggal_pesan,
        'rincian_perawatan' => $reservasi->tindakanPemeriksaan->map(function($t) {
            return [
                'nama' => $t->masterTindakan->tindakan ?? 'Tindakan Medis',
                'harga' => $t->biaya ?? $t->masterTindakan->biaya_tindakan
            ];
        }),
        'biaya_transport' => $biayaTransport,
        'subtotal' => $subTotal,
        'uang_booking' => -$uangMuka, // Minus untuk tampilan UI
        'total_akhir' => max(0, $sisaTagihan), // Tidak boleh minus
        'status_lunas' => ($reservasi->status_pembayaran == 'lunas')
    ];

    return response()->json(['data' => $dataInvoice]);
}
}
