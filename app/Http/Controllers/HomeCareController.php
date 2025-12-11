<?php

namespace App\Http\Controllers;

use App\Services\HomeCareService;
use App\Models\HomeCareReservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeCareController extends Controller
{
    private $reservationService;

    public function __construct(HomeCareService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function calculateCost(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $result = $this->reservationService->calculateCost(
                $request->latitude,
                $request->longitude
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getMasterJadwal(Request $request)
    {
        try {
            $tanggal = $request->query('tanggal'); // optional YYYY-MM-DD

            if ($tanggal) {
                $jadwal = $this->reservationService->getAvailableSchedulesForDate($tanggal);
            } else {
                $jadwal = $this->reservationService->getAvailableSchedules();
            }

            return response()->json(['data' => $jadwal]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function storeBooking(Request $request)
    {
        $request->validate([
            'rekam_medis_id' => 'required|exists:rekam_medis,id',
            'master_jadwal_id' => 'required|exists:master_jadwal,id',
            'tanggal' => 'required|date_format:Y-m-d',
            'keluhan' => 'required|string|max:500',
            'latitude_pasien' => 'required|numeric',
            'longitude_pasien' => 'required|numeric',
            'alamat_lengkap' => 'required|string',
            'metode_pembayaran' => 'required|in:transfer,qris,midtrans',
        ]);

        try {
            Log::info("🔵 storeBooking HomeCare called", $request->all());

            //  mengembalikan snap_token dan redirect_url
            $result = $this->reservationService->createReservation($request->all());

            Log::info("✅ Booking HomeCare created successfully", ['id' => $result['reservation']->id]);

            return response()->json([
                'message' => 'Booking berhasil disimpan.',
                'data' => $result['reservation'],
                'payment_info' => $result['payment_info']
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
        try {
            $result = $this->reservationService->getPaymentHistory($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
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
    // --- FITUR BARU: ENDPOINTS ---

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:homecare_reservasi,id',
            'status' => 'required|string',
            'total_biaya_tindakan' => 'numeric|min:0'
        ]);

        try {
            $result = $this->reservationService->updateStatusPemeriksaan(
                $request->id,
                $request->status,
                $request->total_biaya_tindakan ?? 0
            );
            return response()->json(['message' => 'Status berhasil diperbarui', 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function createSettlement(Request $request)
    {
        $request->validate(['id' => 'required|exists:homecare_reservasi,id']);

        try {
            $result = $this->reservationService->createSettlementTransaction($request->id);
            return response()->json(['message' => 'Link pelunasan generated', 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}