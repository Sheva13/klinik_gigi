<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Import Service & Models
use App\Services\Payment\MidtransService;
use App\Models\HomeCareReservasi;
use App\Models\HomeCareTracking;
use App\Models\Reservasi; // Model Klinik

class MidtransWebhookController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function handle(Request $request)
    {    

        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;
        
        Log::info("🔔 Webhook Midtrans Masuk: {$orderId}", ['status' => $payload['transaction_status'] ?? '-']);

        if (!$orderId) {
            return response()->json(['message' => 'Invalid Payload'], 400);
        }

        // 1. Validasi Signature (Keamanan)
        // Pastikan request benar-benar dari Midtrans
        if (!$this->midtransService->verifySignature(
            $orderId, 
            $payload['status_code'], 
            $payload['gross_amount'], 
            $payload['signature_key']
        )) {
            Log::error("❌ Invalid Signature Key untuk Order: $orderId");
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        // 2. Deteksi Tipe Transaksi & Cari Data di Database
        $transaksi = null;
        $tipeTransaksi = '';

        if (Str::startsWith($orderId, 'HC-')) {
            // --- LOGIC HOME CARE ---
            $transaksi = HomeCareReservasi::where('no_pemeriksaan', $orderId)->first();
            $tipeTransaksi = 'HOME_CARE';
        } 
        elseif (Str::startsWith($orderId, 'RSV-')) {
            // --- LOGIC KLINIK ---
            $transaksi = Reservasi::where('no_pemeriksaan', $orderId)->first();
            $tipeTransaksi = 'KLINIK';
        }

        // Jika data tidak ditemukan di kedua tabel
        if (!$transaksi) {
            Log::error("❌ Order ID tidak ditemukan di database: $orderId");
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 3. Proses Update Status (Menggunakan DB Transaction)
        $transactionStatus = $payload['transaction_status'];
        
        DB::transaction(function () use ($transaksi, $transactionStatus, $tipeTransaksi) {
            $statusAwal = $transaksi->status_pembayaran;
            $keteranganLog = '';

            // --- A. Logic Status Pembayaran ---
            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                // PEMBAYARAN BERHASIL
                $transaksi->status_pembayaran = 'lunas';
                
                // Update Status Operasional
                $transaksi->status = 'Menunggu Dokter'; 
                $transaksi->status_reservasi = 'menunggu';
                
                $keteranganLog = 'Pembayaran lunas via Midtrans.';

            } else if ($transactionStatus == 'expire' || $transactionStatus == 'cancel' || $transactionStatus == 'deny') {
                // PEMBAYARAN GAGAL
                $transaksi->status_pembayaran = 'gagal';
                $transaksi->status_reservasi = 'dibatalkan';
                $transaksi->status = 'Dibatalkan';
                
                $keteranganLog = 'Pembayaran gagal/kadaluarsa.';

            } else if ($transactionStatus == 'pending') {
                // MENUNGGU PEMBAYARAN
                $transaksi->status_pembayaran = 'menunggu_pembayaran';
                $keteranganLog = 'Menunggu pembayaran.';
            }

            // --- B. Simpan Perubahan jika ada update ---
            if ($transaksi->isDirty()) {
                $transaksi->save();
                Log::info("✅ Status {$tipeTransaksi} {$transaksi->no_pemeriksaan} diupdate menjadi: " . $transaksi->status_pembayaran);

                // --- C. Khusus Home Care: Catat Tracking ---
                // Kita hanya mencatat tracking jika tipe-nya HomeCare (karena Klinik tidak punya tabel tracking ini)
                if ($tipeTransaksi === 'HOME_CARE' && !empty($keteranganLog)) {
                    // Cek agar tidak duplikat tracking untuk status yang sama
                    $exists = HomeCareTracking::where('id_periksa', $transaksi->id)
                        ->where('keterangan', $keteranganLog)
                        ->exists();

                    if (!$exists) {
                        HomeCareTracking::create([
                            'id_periksa' => $transaksi->id,
                            'status_tracking' => 'assigned', // Sesuaikan enum di DB
                            'keterangan' => $keteranganLog,
                            'waktu' => now()
                        ]);
                    }
                }
            }
        });

        return response()->json(['message' => 'Webhook processed successfully']);
    }
}