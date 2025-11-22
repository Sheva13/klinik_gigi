<?php

namespace App\Http\Controllers;

use App\Models\BiayaTambahan;
use App\Models\DataPasien;
use App\Models\HomeCareTracking;
use App\Models\JadwalHarian;
use App\Models\MasterJadwal;
use App\Models\MpUser;
use App\Models\RekamMedis; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class HomeCareController extends Controller
{
     /**
     * Menggunakan Rumus Haversine untuk menghitung jarak dan biaya.
     * Menggantikan penggunaan Google Maps API eksternal.
     *
     * @param float $userLat Latitude pasien
     * @param float $userLng Longitude pasien
     * @return array{'jarakDalamKm': float, 'biayaJarak': int}
     */
    private function calculateDistanceAndCost($userLat, $userLng)
    {
        // --- Konfigurasi dari Environment ---
        // Anda HARUS menambahkan nilai ini ke file .env Anda:
        // CLINIC_LAT=-6.806433
        // CLINIC_LNG=110.842188
        // HOMECARE_HARGA_PER_KM=5000
        // HOMECARE_BIAYA_DASAR=100000 (Opsional untuk estimasi)
        
        $clinicLat = env('CLINIC_LAT', -6.9961); // Default koordinat klinik diperbarui
        $clinicLng = env('CLINIC_LNG', 110.4191); // Default koordinat klinik diperbarui
        $hargaPerKm = env('HOMECARE_HARGA_PER_KM', 5000); 

        // RUMUS HAVERSINE (Menghitung jarak 2 titik koordinat bumi)
        $earthRadius = 6371; // Radius bumi dalam KM
        $dLat = deg2rad($clinicLat - $userLat);
        $dLon = deg2rad($clinicLng - $userLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($userLat)) * cos(deg2rad($clinicLat)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Jarak dalam KM

        // Biaya jarak: Jarak (dibulatkan ke atas) dikalikan harga per KM
        $biayaJarak = ceil($distance) * $hargaPerKm;

        return [
            'jarakDalamKm' => $distance,
            'biayaJarak' => (int) $biayaJarak // Pastikan integer untuk biaya
        ];
    }

    // 1. Endpoint untuk Estimasi Biaya Jarak (Dipanggil oleh Frontend)
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
        
        // Asumsi biaya dasar Home Care (Jika ada)
        $biayaDasarHomeCare = env('HOMECARE_BIAYA_DASAR', 100000); 

        return response()->json([
            'status' => 'success',
            'data' => [
                'jarak_km' => round($calculation['jarakDalamKm'], 2),
                'biaya_transport' => $calculation['biayaJarak'],
                'estimasi_total' => $calculation['biayaJarak'] + $biayaDasarHomeCare
            ]
        ]);
    }
    // === Mendapatkan Jadwal ===
    // Mengambil semua template jadwal dokter (bukan jadwal harian)
    public function getMasterJadwal()
    {
        // Mengambil jadwal, termasuk data dokter dan spesialisnya
        $jadwal = MasterJadwal::with(['dokter.spesialis', 'poli'])
                    ->where('quota', '>', 0) // Hanya tampilkan yg ada quota
                    ->get();

        return response()->json(['data' => $jadwal]);
    }

    // === Membuat Booking (Inti Logika) ===
    public function storeBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'master_jadwal_id' => 'required|integer|exists:master_jadwal,id',
            'tanggal' => 'required|date_format:Y-m-d',
            'keluhan' => 'required|string|max:500',
            'latitude_pasien' => 'required|numeric',
            'longitude_pasien' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Mendapatkan user yang login
        $user = Auth::user(); 
        if (!$user || !$user->rekam_medis_id) {
            return response()->json(['error' => 'User tidak valid atau tidak memiliki rekam medis.'], 403);
        }

        // (Perhitungan Sisi Server) ---
        // Anda harus menyimpan Google Maps API Key di file .env
        $apiKey = env('GOOGLE_MAPS_API_KEY'); 
        $originKlinik = env('KLINIK_COORDINATES'); // Cth: "-6.9830, 110.4091"
        $destination = $request->latitude_pasien . ',' . $request->longitude_pasien;

        try {
            $response = Http::get("https://maps.googleapis.com/maps/api/distancematrix/json", [
                'origins' => $originKlinik,
                'destinations' => $destination,
                'key' => $apiKey,
                'units' => 'metric' // untuk kilometer
            ]);
            // Menggunakan fungsi internal Haversine untuk kalkulasi
            $calculation = $this->calculateDistanceAndCost(
                $request->latitude_pasien,
                $request->longitude_pasien
            );
            $biayaJarak = $calculation['biayaJarak'];

            $data = $response->json();
            
            if ($data['status'] != 'OK' || !isset($data['rows'][0]['elements'][0]['status']) || $data['rows'][0]['elements'][0]['status'] != 'OK') {
                throw new \Exception('Gagal menghitung jarak: ' . ($data['error_message'] ?? 'Alamat tidak ditemukan atau API Key salah'));
            }

            // Jarak dalam METER
            $jarakDalamMeter = $data['rows'][0]['elements'][0]['distance']['value'];
            $jarakDalamKm = $jarakDalamMeter / 1000;

            // --- Aturan Bisnis Anda ---
            // Simpan aturan ini di config atau .env agar mudah diubah
            $hargaPerKm = env('HOMECARE_HARGA_PER_KM', 5000); 
            $uangMuka = env('HOMECARE_UANG_MUKA', 25000);
            
            $biayaJarak = ceil($jarakDalamKm) * $hargaPerKm; // Dibulatkan ke atas

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghitung biaya jarak: ' . $e->getMessage()], 500);
        }


        // Transaksi Database untuk memastikan semua data aman
        return DB::transaction(function () use ($request, $user, $uangMuka, $biayaJarak) {
            
            // membuat atau temukan Jadwal Harian
            $jadwalHarian = JadwalHarian::firstOrCreate(
                [
                    'master_jadwal_id' => $request->master_jadwal_id,
                    'tanggal' => $request->tanggal,
                ]
            );

            // Membuat Booking (Kunjungan) di DataPasien
            $booking = DataPasien::create([
                'id_jadwal' => $jadwalHarian->id,
                'rekam_medis_id' => $user->rekam_medis_id, // Sesuai Pilihan A (Integer)
                'status' => 0, // 0 = Menunggu Verifikasi Admin
                'keluhan' => $request->keluhan,
                'latitude_pasien' => $request->latitude_pasien,
                'longitude_pasien' => $request->longitude_pasien,
            ]);

            // Menyimpan Biaya-biaya Awal
            // (Sesuai Q4, kita simpan DP dan Biaya Jarak)
            BiayaTambahan::create([
                'id_periksa' => $booking->id,
                'komponen' => 'UANG_MUKA',
                'biaya' => $uangMuka,
            ]);

            BiayaTambahan::create([
                'id_periksa' => $booking->id,
                'komponen' => 'BIAYA_JARAK',
                'biaya' => $biayaJarak,
            ]);

            // membuat entri tracking pertama (Modul 4)
            HomeCareTracking::create([
                'id_periksa' => $booking->id,
                'status_tracking' => 'Booking dibuat, menunggu verifikasi admin.',
                'timestamp' => now()
            ]);

            return response()->json([
                'message' => 'Booking berhasil dibuat. Menunggu verifikasi admin.',
                'data' => $booking->load(['jadwalHarian.masterJadwal.dokter', 'biayaTambahan'])
            ], 201); // 201 = Created
        });
    }

    // Pembayaran DP
    // Ini adalah endpoint untuk USER untuk konfirmasi pembayaran
    // (Sesuai Q4, user hanya konfirmasi, tidak ada payment gateway)
    public function confirmPayment(Request $request, $id)
    {
        $booking = DataPasien::find($id);

        if (!$booking) {
             return response()->json(['error' => 'Booking tidak ditemukan.'], 404);
        }

        // memastikan user yg login adalah pemilik booking
        if (Auth::user()->rekam_medis_id != $booking->rekam_medis_id) {
             return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        // User hanya bisa konfirm jika status = 1
        if ($booking->status != 1) {
            return response()->json(['error' => 'Booking ini tidak sedang menunggu pembayaran.'], 422);
        }

        // Ubah status ke 2 (Terkonfirmasi)
        $booking->status = 2; // 2 = Pembayaran Diterima, Terkonfirmasi
        $booking->save();

        // Update tracking
        HomeCareTracking::create([
            'id_periksa' => $booking->id,
            'status_tracking' => 'Pembayaran DP dikonfirmasi. Menunggu jadwal kunjungan.',
            'timestamp' => now()
        ]);

        return response()->json(['message' => 'Konfirmasi pembayaran berhasil.', 'data' => $booking]);
    }
    
    // === CATATAN: Endpoint untuk ADMIN ===
    // Anda perlu endpoint terpisah (mungkin di grup Admin)
    // untuk mengubah status 0 -> 1 (Menyetujui Jadwal)
    
    /*
    public function approveBookingByAdmin(Request $request, $id)
    {
        // ... (Logika validasi Admin) ...
        $booking = DataPasien::find($id);
        if ($booking && $booking->status == 0) {
            $booking->status = 1; // 1 = Disetujui, Menunggu Pembayaran
            $booking->save();
            
            HomeCareTracking::create([
                'id_periksa' => $booking->id,
                'status_tracking' => 'Jadwal disetujui admin. Menunggu pembayaran DP.',
                'timestamp' => now()
            ]);
            return response()->json(['message' => 'Booking disetujui.']);
        }
        return response()->json(['error' => 'Booking tidak dalam status menunggu.'], 422);
    }
    */


    // Tracking
    public function getTrackingHistory($id)
    {
        $booking = DataPasien::find($id);

        if (!$booking) {
             return response()->json(['error' => 'Booking tidak ditemukan.'], 404);
        }
        
        // memastikan user yg login adalah pemilik booking
        if (Auth::user()->rekam_medis_id != $booking->rekam_medis_id) {
             return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $trackingHistory = HomeCareTracking::where('id_periksa', $id)
                            ->orderBy('timestamp', 'asc')
                            ->get();
        
        return response()->json(['data' => $trackingHistory]);
    }
}