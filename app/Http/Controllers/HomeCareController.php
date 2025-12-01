<?php

namespace App\Http\Controllers;

use App\Models\BiayaTambahan;
use App\Models\Reservasi; 
use App\Models\HomeCareTracking;
use App\Models\JadwalHarian;
use App\Models\MasterJadwal;
// use App\Models\DataPasien; // DINONAKTIFKAN
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeCareController extends Controller
{
    // Konfigurasi Hardcode
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

        $biayaJarak = ceil($distance) * $tarif;

        return [
            'jarakDalamKm' => $distance,
            'biayaJarak' => (int) $biayaJarak
        ];
    }

    // 1. API untuk Cek Ongkir
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

    // 2. API Get Jadwal
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

        $user = $request->user();
        if (!$user) {
             $user = Auth::guard('sanctum')->user();
        }
        if (!$user) {
             return response()->json(['error' => 'Unauthorized: User tidak ditemukan.'], 401);
        }

        // BYPASS TABEL DATA_PASIEN
        $pasienIdUntukDisimpan = $user->id;

        $calculation = $this->calculateDistanceAndCost(
            $request->latitude_pasien,
            $request->longitude_pasien
        );
        $biayaJarak = $calculation['biayaJarak'];
        $dpAmount = env('HOMECARE_UANG_MUKA', $this->uangMuka);

        return DB::transaction(function () use ($request, $user, $pasienIdUntukDisimpan, $dpAmount, $biayaJarak) {
            
            $jadwalHarian = JadwalHarian::firstOrCreate(
                [
                    'kode_jadwal' => $request->master_jadwal_id,
                    'tanggal' => $request->tanggal,
                ],
                ['validasi' => 0] 
            );

            $masterJadwal = MasterJadwal::find($request->master_jadwal_id);
            if (!$masterJadwal) {
                throw new \Exception('Master jadwal tidak ditemukan');
            }

            // Simpan Reservasi
            $reservasi = Reservasi::create([
                'no_pemeriksaan' => 'HC-' . time() . '-' . rand(1000, 9999), 
                'pasien_id' => $pasienIdUntukDisimpan, 
                'dokter_id' => $masterJadwal->dokter_id, 
                'jadwal_id' => $jadwalHarian->id, 
                'tanggal_pesan' => $request->tanggal, 
                'waktu_pesan' => now()->toTimeString(), 
                'jam_mulai' => $masterJadwal->jam_mulai, 
                'jam_selesai' => $masterJadwal->jam_selesai, 
                'tipe_layanan' => 'home_care',
                'jenis_pasien' => 'Umum', 
                'status' => 0, 
                'status_reservasi' => 'Menunggu', 
                'keluhan' => $request->keluhan,
                'latitude' => $request->latitude_pasien,
                'longitude' => $request->longitude_pasien,
                'alamat_lengkap' => $request->alamat_lengkap,
                'biaya_transport' => $biayaJarak,
                'biaya_reservasi' => env('HOMECARE_BIAYA_DASAR', 35000), 
                'pembayaran_total' => $biayaJarak + env('HOMECARE_BIAYA_DASAR', 35000), 
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'Belum', 
            ]);

            // Simpan Rincian Biaya
            BiayaTambahan::create([
                'id_periksa' => $reservasi->id, 
                'komponen' => 'UANG_MUKA',
                'biaya' => $dpAmount,
            ]);

            BiayaTambahan::create([
                'id_periksa' => $reservasi->id,
                'komponen' => 'BIAYA_JARAK',
                'biaya' => $biayaJarak,
            ]);

            // D. Mulai Tracking (PERBAIKAN DISINI)
            // 'status_tracking' harus sesuai ENUM di database (assigned, otw, arrived, progress, finished)
            // Keterangan panjang masuk ke kolom 'keterangan'
            // timestamp ganti jadi 'waktu'
            HomeCareTracking::create([
                'id_periksa' => $reservasi->id,
                'status_tracking' => 'assigned', // Default awal
                'keterangan' => 'Booking berhasil dibuat. Menunggu pembayaran.',
                'waktu' => now()
            ]);

            return response()->json([
                'message' => 'Booking berhasil disimpan.',
                'data' => $reservasi->load(['jadwalHarian.masterJadwal.dokter'])
            ], 201);
        });
    }

    // 4. Konfirmasi Pembayaran
    public function confirmPayment(Request $request, $id)
    {
        $reservasi = Reservasi::find($id);

        if (!$reservasi) {
             return response()->json(['error' => 'Booking tidak ditemukan.'], 404);
        }
        
        $reservasi->status = 1; 
        $reservasi->save();

        // Update Tracking (Perbaikan ENUM & Kolom)
        HomeCareTracking::create([
            'id_periksa' => $reservasi->id,
            'status_tracking' => 'assigned', // Masih tahap assigned
            'keterangan' => 'Pembayaran DP berhasil. Menunggu konfirmasi Admin.',
            'waktu' => now()
        ]);

        return response()->json(['message' => 'Pembayaran berhasil.', 'data' => $reservasi]);
    }

    // 5. History Tracking
    public function getTrackingHistory($id)
    {
        $history = HomeCareTracking::where('id_periksa', $id)
                                   ->orderBy('waktu', 'desc') // Ganti timestamp jadi waktu
                                   ->get();
        
        return response()->json(['data' => $history]);
    }

    public function getInvoice($id)
    {
        $reservasi = Reservasi::with(['tindakanPemeriksaan.masterTindakan', 'biayaTambahan'])->find($id);
        if (!$reservasi) return response()->json(['error' => 'Data tidak ditemukan'], 404);

        $totalTindakan = $reservasi->tindakanPemeriksaan->sum(function($item) {
            return $item->biaya ?? $item->masterTindakan->biaya_tindakan;
        });

        $biayaTransport = $reservasi->biaya_transport; 
        $subTotal = $totalTindakan + $biayaTransport;

        $uangMuka = $reservasi->biayaTambahan
                    ->where('komponen', 'UANG_MUKA') 
                    ->sum('biaya');

        $sisaTagihan = $subTotal - $uangMuka;

        $dataInvoice = [
            'nama_pasien' => Auth::user()->name ?? 'Pasien', 
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
            'uang_booking' => -$uangMuka, 
            'total_akhir' => max(0, $sisaTagihan), 
            'status_lunas' => ($reservasi->status_pembayaran == 'Lunas')
        ];

        return response()->json(['data' => $dataInvoice]);
    }

    // [BARU] Proses Bayar Pelunasan
    public function paySettlement(Request $request, $id)
    {
        $reservasi = Reservasi::find($id);
        
        if ($reservasi->status_pembayaran == 'Lunas') { 
            return response()->json(['message' => 'Tagihan sudah lunas sebelumnya.']);
        }

        $reservasi->status_pembayaran = 'Lunas'; 
        $reservasi->status = 'Selesai';
        $reservasi->save();

        // Update Tracking (Perbaikan ENUM & Kolom)
        HomeCareTracking::create([
            'id_periksa' => $reservasi->id,
            'status_tracking' => 'finished', // Gunakan finished karena sudah lunas/selesai
            'keterangan' => 'Pelunasan berhasil. Layanan selesai.',
            'waktu' => now()
        ]);

        return response()->json(['message' => 'Pembayaran pelunasan berhasil.']);
    }
}