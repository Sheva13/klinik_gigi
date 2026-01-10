<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Reservasi;
use App\Models\HomeCareReservasi;
use App\Services\Payment\MidtransService;
use Exception;

class AdminPaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    protected function success($msg, $data = null) {
        return response()->json(['status' => 'success', 'message' => $msg, 'data' => $data]);
    }
    protected function error($msg, $code = 400) {
        return response()->json(['status' => 'error', 'message' => $msg], $code);
    }
    
    // ============================================================
    // 1. INDEX (DAFTAR SEMUA TRANSAKSI DARI DB)
    // ============================================================
    public function index(Request $request)
    {
        // Mengambil transaksi Klinik yang memiliki pembayaran
        $reservasiKlinik = Reservasi::whereIn('status_pembayaran', ['lunas', 'terverifikasi', 'gagal', 'menunggu_pembayaran'])
            ->select(
                'no_pemeriksaan', 
                'pembayaran_total', 
                'status_pembayaran', 
                'metode_pembayaran',
                'created_at'
            )
            ->get();
        
        // Mengambil transaksi Home Care (asumsi status lunas/gagal/menunggu ada di status_booking)
        $reservasiHomeCare = HomeCareReservasi::whereIn('status_booking', ['lunas', 'gagal', 'belum_lunas'])
            ->select(
                'no_pemeriksaan', 
                'pembayaran_total', 
                'status_booking AS status_pembayaran', 
                'metode_pembayaran',
                'created_at'
            )
            ->get();

        // Gabungkan semua data transaksi
        $data = $reservasiKlinik->map(function($item) {
            $item['tipe_layanan'] = 'Klinik';
            return $item;
        })->merge($reservasiHomeCare->map(function($item) {
            $item['tipe_layanan'] = 'Home Care';
            return $item;
        }))->sortByDesc('created_at')->values();


        return view('payment.index', compact('data'));
    }

    // ============================================================
    // 2. FETCH DETAIL MIDTRANS (Real-time Status)
    // ============================================================
    public function getMidtransDetail($orderId)
    {
        try {
            // Panggil Midtrans API untuk status terkini
            $midtransResponse = $this->midtransService->getTransactionStatus($orderId);
            
            // Ambil data lokal dari database (untuk perbandingan)
            $localData = null;
            if (Str::startsWith($orderId, 'RSV-')) {
                $localData = Reservasi::where('no_pemeriksaan', $orderId)->first();
            } elseif (Str::startsWith($orderId, 'HC-') || Str::startsWith($orderId, 'PL-')) {
                // Untuk Home Care, perlu logic tambahan jika Order ID berbeda dari no_pemeriksaan
                if (Str::startsWith($orderId, 'PL-')) {
                    if (preg_match('/^PL-(.+)-(\d+)$/', $orderId, $matches)) {
                        $orderIdAsli = $matches[1];
                    }
                }
                $localData = HomeCareReservasi::where('no_pemeriksaan', $orderIdAsli ?? $orderId)->first();
            }

            return $this->success('Detail Midtrans berhasil diambil', [
                'order_id' => $orderId,
                'midtrans_status' => $midtransResponse->transaction_status ?? 'NOT_FOUND',
                'metode_pembayaran_midtrans' => $midtransResponse->payment_type ?? '-',
                'gross_amount' => $midtransResponse->gross_amount ?? 0,
                'DB_status' => $localData->status_pembayaran ?? $localData->status_booking ?? 'NOT_FOUND_DB'
            ]);

        } catch (Exception $e) {
            Log::error("Midtrans Detail Error for $orderId: " . $e->getMessage());
            return $this->error('Gagal mengambil detail dari Midtrans API.', 500);
        }
    }

    // ============================================================
    // 3. MANUAL RESYNC DARI MIDTRANS (Troubleshooting)
    // ============================================================
    public function resyncStatus($orderId)
    {
        // Logika di sini akan memanggil Midtrans API (seperti getMidtransDetail)
        // Kemudian, logic ini akan meniru proses yang dilakukan di MidtransWebhookController
        // untuk mengupdate status di DB, antrian, dan kasir secara manual.
        // Implementasi penuhnya akan bergantung pada struktur response Midtrans dan logic webhook.
        
        return $this->success("Resync status untuk $orderId sedang diproses.");
    }

}