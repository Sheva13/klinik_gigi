<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Services\HomeCareService;
use App\Models\HomeCareReservasi;
use App\Models\MasterPromo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeCareController extends Controller
{
    private $midtransService;
    private $reservationService;

    public function __construct(HomeCareService $reservationService, \App\Services\Payment\MidtransService $midtransService)
    {
        $this->reservationService = $reservationService;
        $this->midtransService = $midtransService;
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
            $kodePoli = $request->query('kode_poli');

            if ($tanggal) {
                $jadwal = $this->reservationService->getAvailableSchedulesForDate($tanggal, $kodePoli);
            } else {
                $jadwal = $this->reservationService->getAvailableSchedules($kodePoli);
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
            'jenis_keluhan' => 'required|string',
            'jenis_keluhan_lainnya' => 'nullable|string',
            'promo_id' => 'nullable|exists:master_promo,id',
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

            // --- ACTIVE CHECK (FOR LOCALHOST / WEBHOOK FAILURES) ---
            // Jika status di DB masih belum lunas, coba tanya langsung ke Midtrans
            if ($reservasi->status_booking === 'belum_lunas') {
                $midtransStatus = $this->midtransService->getTransactionStatus($reservasi->no_pemeriksaan);

                if ($midtransStatus && ($midtransStatus['transaction_status'] == 'capture' || $midtransStatus['transaction_status'] == 'settlement')) {
                    // Update Status
                    $reservasi->status_booking = 'lunas';
                    $reservasi->status = 'Menunggu Konfirmasi'; // Fix: Jangan 'Menunggu Dokter' agar App tidak bilang OTW
                    $reservasi->status_reservasi = 'menunggu_konfirmasi'; // Fix: Samakan dengan enum di Admin Panel
                    $reservasi->save();

                    // Tambah Poin Manual (Copy Logic from Webhook)
                    $poinDidapat = floor(($midtransStatus['gross_amount'] ?? 0) / 10000);
                    if ($reservasi->pasien_id && $poinDidapat > 0) {
                        \Illuminate\Support\Facades\DB::table('users')
                            ->where('user_id', $reservasi->pasien_id)
                            ->increment('poin', $poinDidapat);
                    }
                }
            } else if ($reservasi->status_pelunasan !== 'lunas') {
                // Cek pelunasan (PL-)
                // Deterministic ID logic:
                $settlementOrderId = 'PL-' . $reservasi->no_pemeriksaan;
                $midtransStatus = $this->midtransService->getTransactionStatus($settlementOrderId);

                if ($midtransStatus && ($midtransStatus['transaction_status'] == 'capture' || $midtransStatus['transaction_status'] == 'settlement')) {
                    // Update Status Pelunasan
                    $reservasi->status_pelunasan = 'lunas';
                    // status_booking tetap 'lunas'
                    // status_reservasi bisa 'selesai' atau tetap?
                    // Biasanya setelah lunas jadi 'selesai'
                    $reservasi->status = 'Selesai';
                    $reservasi->status_reservasi = 'selesai';
                    $reservasi->save();

                    // Tambah Poin Manual (Pelunasan)
                    $poinDidapat = floor(($midtransStatus['gross_amount'] ?? 0) / 10000);
                    if ($reservasi->pasien_id && $poinDidapat > 0) {
                        \Illuminate\Support\Facades\DB::table('users')
                            ->where('user_id', $reservasi->pasien_id)
                            ->increment('poin', $poinDidapat);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $reservasi->id,
                    'no_pemeriksaan' => $reservasi->no_pemeriksaan,
                    'status_pembayaran' => ($reservasi->status_booking === 'belum_lunas') ? 'menunggu_pembayaran' : $reservasi->status_booking,
                    'status_reservasi' => $reservasi->status_reservasi,
                    'status_pelunasan' => $reservasi->status_pelunasan,
                    'total_biaya_tindakan' => $reservasi->total_biaya_tindakan ?? 0,
                    // Additional Info for Tracking Screen
                    'nama_dokter' => $reservasi->jadwalHarian->masterJadwal->dokter->nama ?? 'Dokter HomeCare',
                    'jadwal_tanggal' => $reservasi->jadwalHarian->tanggal ?? $reservasi->tanggal_pesan,
                    'jadwal_jam' => $reservasi->jadwalHarian->masterJadwal->jam_mulai ?? '-',
                    'estimasi_tiba' => '15 menit',
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


    // --- FITUR BARU: API POIN & PROMO ---

    public function getPromos(Request $request)
    {
        $type = $request->query('type', 'booking'); // booking | settlement

        $dateNow = Carbon::now('Asia/Jakarta');
        $query = MasterPromo::query();
        // Filter by Validity Date
        $query->whereDate('tanggal_mulai', '<=', $dateNow)
              ->whereDate('tanggal_selesai', '>=', $dateNow);

        // Filter by Target Transaksi (Booking vs Pelunasan)
        if ($type != 'all') {
            $target = ($type == 'settlement') ? 'pelunasan' : 'booking';
            $query->whereIn('target_transaksi', [$target, 'semua']);
        }
        // Booking boleh semua (inclusive free_transport)
        $query->orderBy('id', 'desc');

        $promos = $query->get();
        return response()->json(['data' => $promos]);
    }

    public function getUserPoints(Request $request)
    {
        $userId = $request->query('user_id'); // Or via Auth::id() if authenticated
        // Fallback checks
        if (!$userId)
            return response()->json(['poin' => 0]);

        // Use Query Builder for consistency with Webhook and reliability with String IDs
        $poin = \Illuminate\Support\Facades\DB::table('users')
            ->where('user_id', $userId)
            ->value('poin');

        return response()->json(['poin' => (int) $poin]);
    }

    // --- FITUR BARU: ENDPOINTS EXISTING ---

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
        $request->validate([
            'id' => 'required|exists:homecare_reservasi,id',
            'promo_id' => 'nullable|exists:master_promo,id'
        ]);

        try {
            $result = $this->reservationService->createSettlementTransaction($request->id, $request->promo_id);
            return response()->json(['message' => 'Link pelunasan generated', 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function getPointHistory(Request $request)
    {
        $userId = $request->query('user_id'); 
        // Fallback checks
        if (!$userId) return response()->json(['data' => []]);

        $history = \App\Models\PointHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
        public function showImage($path)
    {
        $path = storage_path('app/public/' . $path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        return response($file, 200)->header("Content-Type", $type);
    }
}