<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Services\HomeCareService;
use App\Models\HomeCareReservasi;
use App\Models\MasterPromo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\HomeCareTracking;
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

    // 1. API untuk Cek Ongkir
    public function calculateCost(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $result = $this->reservationService->calculateCost(
            $request->latitude,
            $request->longitude
        );

        return response()->json($result);
    }

    // 2. API Get Jadwal
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

    // 3. API Booking (Inti Transaksi)
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
            Log::info("🔍 Polling Check for {$reservasi->no_pemeriksaan}. DB Status: {$reservasi->status_booking}");

            // Jika status di DB masih belum lunas, coba tanya langsung ke Midtrans
            if ($reservasi->status_booking === 'belum_lunas') {
                $midtransStatus = $this->midtransService->getTransactionStatus($reservasi->no_pemeriksaan);
                Log::info("🔍 Midtrans Response: " . json_encode($midtransStatus));

                if ($midtransStatus && ($midtransStatus['transaction_status'] == 'capture' || $midtransStatus['transaction_status'] == 'settlement')) {
                    Log::info("✅ Payment Success Detected via Polling!");
                    // Update Status
                    $reservasi->status_booking = 'lunas';
                    $reservasi->status = 'Menunggu Konfirmasi';
                    $reservasi->status_reservasi = 'menunggu_konfirmasi';
                    $reservasi->save();

                    // Tambah Poin Manual (Booking) - WITH DUPLICATE CHECK
                    $poinDidapat = floor(($midtransStatus['gross_amount'] ?? 0) / 10000);
                    
                    // Point Logic - CHECK IF ALREADY ADDED
                    if ($reservasi->pasien_id && $poinDidapat > 0) {
                        // Check if points already added for this transaction (use type='earn' for reliable check)
                        $historyExists = \App\Models\PointHistory::where('reference_id', $reservasi->no_pemeriksaan)
                                            ->where('type', 'earn')
                                            ->exists();
                        
                        if (!$historyExists) {
                            try {
                                $user = User::where('user_id', $reservasi->pasien_id)->first();
                                if (!$user) $user = User::where('id', $reservasi->pasien_id)->first();
                                if ($user) {
                                    $user->increment('poin', $poinDidapat);
                                    \App\Models\PointHistory::create([
                                         'user_id' => $user->user_id,
                                         'amount' => $poinDidapat,
                                         'type' => 'earn',
                                         'description' => "Pembayaran Booking HomeCare",
                                         'reference_id' => $reservasi->no_pemeriksaan,
                                    ]);
                                    Log::info("✅ Points added: $poinDidapat for booking " . $reservasi->no_pemeriksaan);
                                }
                            } catch (\Exception $e) {
                                 Log::error("Point Error: " . $e->getMessage());
                            }
                        } else {
                            Log::info("⚠️ Points already added for booking " . $reservasi->no_pemeriksaan . ", skipping.");
                        }
                    }
                }
            } else if ($reservasi->status_pelunasan !== 'lunas') {
                // Cek pelunasan (PL-)
                $settlementOrderId = 'PL-' . $reservasi->no_pemeriksaan;
                $midtransStatus = $this->midtransService->getTransactionStatus($settlementOrderId);

                if ($midtransStatus && ($midtransStatus['transaction_status'] == 'capture' || $midtransStatus['transaction_status'] == 'settlement')) {
                    // Update Status Pelunasan
                    $reservasi->status_pelunasan = 'lunas';
                    $reservasi->status = 'Selesai';
                    $reservasi->status_reservasi = 'selesai';
                    $reservasi->save();

                    // Tambah Poin Manual (Pelunasan) - WITH DUPLICATE CHECK
                    $poinDidapat = floor(($midtransStatus['gross_amount'] ?? 0) / 10000);
                    
                    if ($reservasi->pasien_id && $poinDidapat > 0) {
                        // Check if points already added for this transaction (use type='earn' for reliable check)
                        $historyExists = \App\Models\PointHistory::where('reference_id', $reservasi->no_pemeriksaan)
                                            ->where('type', 'earn')
                                            ->exists();
                        
                        if (!$historyExists) {
                            try {
                                $user = User::where('user_id', $reservasi->pasien_id)->first();
                                if (!$user) $user = User::where('id', $reservasi->pasien_id)->first();

                                if ($user) {
                                    $user->increment('poin', $poinDidapat);
                                    \App\Models\PointHistory::create([
                                         'user_id' => $user->user_id,
                                         'amount' => $poinDidapat,
                                         'type' => 'earn',
                                         'description' => "Pelunasan tagihan via Manual",
                                         'reference_id' => $reservasi->no_pemeriksaan,
                                    ]);
                                    Log::info("✅ Points added: $poinDidapat for pelunasan " . $reservasi->no_pemeriksaan);
                                } else {
                                    Log::error("Point Error: User not found ID: " . $reservasi->pasien_id);
                                }
                            } catch (\Exception $e) {
                                 Log::error("Point Error: " . $e->getMessage());
                            }
                        } else {
                            Log::info("⚠️ Points already added for pelunasan " . $reservasi->no_pemeriksaan . ", skipping.");
                        }
                    }
                }
            }

            // --- RECOVERY LOGIC: Cek jika sudah Lunas tapi Poin Belum Masuk ---
            // (Untuk menangani kasus dimana status update via Webhook sebelum logic poin diperbaiki)
            if ($reservasi->status_pelunasan === 'lunas') {
                 // Cek apakah history poin sudah ada untuk transaksi ini
                 $historyExists = \App\Models\PointHistory::where('reference_id', $reservasi->no_pemeriksaan)
                                    ->where('description', 'LIKE', '%Pelunasan%')
                                    ->exists();
                 
                 if (!$historyExists) {
                     // Hitung poin seharusnya (perlu fetch gross_amount dari midtrans atau hitung dari total_biaya_tindakan)
                     // Kita gunakan total_biaya_tindakan dari DB agar tidak perlu call Midtrans lagi jika memungkinkan
                     $grossAmount = $reservasi->total_biaya_tindakan > 0 ? $reservasi->total_biaya_tindakan : 0;
                     
                     // Fallback charge Midtrans if 0 (mungkin data lama belum ada total_biaya)
                     if ($grossAmount <= 0) {
                         $settlementOrderId = 'PL-' . $reservasi->no_pemeriksaan;
                         $midtransStatus = $this->midtransService->getTransactionStatus($settlementOrderId);
                         $grossAmount = $midtransStatus['gross_amount'] ?? 0;
                     }

                     $poinDidapat = floor($grossAmount / 10000);

                     if ($reservasi->pasien_id && $poinDidapat > 0) {
                        try {
                            $user = User::where('user_id', $reservasi->pasien_id)->first();
                            if (!$user) $user = User::where('id', $reservasi->pasien_id)->first();
                            
                            if ($user) {
                                $user->increment('poin', $poinDidapat);
                                $debugPointMsg = "Recovered: Added $poinDidapat pts";
                                \App\Models\PointHistory::create([
                                     'user_id' => $user->user_id,
                                     'amount' => $poinDidapat,
                                     'type' => 'earn',
                                     'description' => "Pelunasan tagihan via Manual (Recovery)",
                                     'reference_id' => $reservasi->no_pemeriksaan,
                                ]);
                            }
                        } catch (\Exception $e) {
                             Log::error("Point Recovery Error: " . $e->getMessage());
                        }
                     }
                 }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $reservasi->id,
                    'no_pemeriksaan' => $reservasi->no_pemeriksaan,
                    'no_antrian' => $reservasi->no_antrian, // Add Queue Number
                    'status_pembayaran' => ($reservasi->status_booking === 'belum_lunas') ? 'menunggu_pembayaran' : $reservasi->status_booking,
                    'status_reservasi' => $reservasi->status_reservasi,
                    'status_pelunasan' => $reservasi->status_pelunasan,
                    'total_biaya_tindakan' => $reservasi->total_biaya_tindakan ?? 0,
                    'nama_dokter' => $reservasi->dokter->nama ?? 'Dokter HomeCare',
                    'jadwal_tanggal' => $reservasi->tanggal_pesan, 
                    'jadwal_jam' => ($reservasi->jam_mulai && $reservasi->jam_selesai) 
                                    ? "{$reservasi->jam_mulai} - {$reservasi->jam_selesai}" 
                                    : ($reservasi->jam_mulai ?? '-'),
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