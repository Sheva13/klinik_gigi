<?php

namespace App\Http\Controllers;

use App\Services\HomeCareService;
use App\Models\HomeCareReservasi;
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
        $request->validate([
            'rekam_medis_id'    => 'required|exists:rekam_medis,id',
            'master_jadwal_id'  => 'required|exists:master_jadwal,id',
            'tanggal'           => 'required|date_format:Y-m-d',
            'keluhan'           => 'required|string|max:500',
            'latitude_pasien'   => 'required|numeric',
            'longitude_pasien'  => 'required|numeric',
            'alamat_lengkap'    => 'required|string',
            'metode_pembayaran' => 'required|in:transfer,qris,midtrans',
        ]);

        try {
            Log::info("🔵 storeBooking HomeCare called", $request->all());
            
            //  mengembalikan snap_token dan redirect_url
            $result = $this->reservationService->createReservation($request->all());

            Log::info("✅ Booking HomeCare created successfully", ['id' => $result['reservation']->id]);

            return response()->json([
                'message' => 'Booking berhasil disimpan.',
                'data' => $reservasi->load(['jadwalHarian.masterJadwal.dokter'])
            ], 201);

        } catch (\Exception $e) {
            Log::error("❌ storeBooking Error: " . $e->getMessage());
            
            // Validasi HTTP Status Code agar tidak Error 500 karena code 0
            $statusCode = (int) $e->getCode();
            if ($statusCode < 100 || $statusCode > 599) {
                $statusCode = 400; 
            }

            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    // --- Method untuk cek status pembayaran secara manual (Polling Frontend) ---
    public function checkPaymentStatus($id)
    {
        try {
            $reservasi = HomeCareReservasi::find($id);

            if (!$reservasi) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $reservasi->id,
                    'no_pemeriksaan' => $reservasi->no_pemeriksaan,
                    'status_pembayaran' => $reservasi->status_pembayaran, // lunas, menunggu_pembayaran, gagal
                    'status_reservasi' => $reservasi->status_reservasi
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // --- WRAPPER METHODS (Meneruskan ke Service) ---

    public function getTrackingHistory($id)
    {
        $history = HomeCareTracking::where('id_periksa', $id)
                                   ->orderBy('waktu', 'desc') // Ganti timestamp jadi waktu
                                   ->get();
        
        return response()->json(['data' => $history]);
    }

    public function getInvoice($id)
    {
        try {
            $result = $this->reservationService->getInvoice($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function paySettlement(Request $request, $id)
    {
        try {
            $result = $this->reservationService->processSettlement($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function cancelReservation($id)
    {
        try {
            $this->reservationService->cancelReservation($id);
            return response()->json(['message' => 'Reservasi berhasil dibatalkan.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Deprecated: Konfirmasi manual (opsional, jika user transfer manual non-midtrans)
    public function confirmPayment(Request $request, $id)
    {
        try {
            $reservasi = $this->reservationService->confirmPayment($id);
            return response()->json(['message' => 'Pembayaran berhasil dikonfirmasi.', 'data' => $reservasi]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}