<?php

namespace App\Http\Controllers;

use App\Services\HomeCareService;
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
            Log::info("🔵 storeBooking called with data:", $request->all());
            
            $result = $this->reservationService->createReservation($request->all());

            Log::info("✅ Booking created successfully", ['result' => $result]);

            return response()->json([
                'message'      => 'Booking berhasil disimpan.',
                'data'         => $result['reservation'], 
                'payment_info' => $result['payment_info']
            ], 201);

        } catch (\Exception $e) {
            Log::error("❌ storeBooking Error: " . $e->getMessage());
            Log::error("Error Code: " . $e->getCode());
            Log::error("Stack Trace: " . $e->getTraceAsString());
            
            // 1. Ambil kode error
            $statusCode = $e->getCode();

            // 2. Pastikan tipe datanya INTEGER (Angka), bukan String
            $statusCode = (int) $statusCode;

            // 3. Validasi range HTTP Code (harus antara 100 - 599)
            // Jika kodenya 0 (default Exception) atau aneh, ubah jadi 400 (Bad Request)
            if ($statusCode < 100 || $statusCode > 599) {
                $statusCode = 400; 
            }

            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    // ... Method lain (confirmPayment, getTrackingHistory, dll) TETAP SAMA ...
    // ... Silakan copy-paste method sisanya dari file asli Anda jika diperlukan ...
    
    public function confirmPayment(Request $request, $id)
    {
        try {
            $reservasi = $this->reservationService->confirmPayment($id);
            return response()->json(['message' => 'Pembayaran berhasil.', 'data' => $reservasi]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

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
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function paySettlement(Request $request, $id)
    {
        try {
            $result = $this->reservationService->processSettlement($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function cancelReservation($id)
    {
        try {
            $this->reservationService->cancelReservation($id);
            return response()->json(['message' => 'Reservasi berhasil dibatalkan.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function midtransWebhook(Request $request)
    {
        try {
            // Midtrans mengirim data via POST body
            $this->reservationService->handleMidtransCallback($request->all());
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            // Log error agar bisa didebug jika midtrans gagal lapor
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function checkPaymentStatus($id)
    {
        try {
            $reservasi = \App\Models\HomeCareReservasi::find($id);

            if (!$reservasi) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $reservasi->id,
                    'status_pembayaran' => $reservasi->status_pembayaran, // 'lunas', 'menunggu_pembayaran', dll
                    'status_reservasi' => $reservasi->status_reservasi
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}