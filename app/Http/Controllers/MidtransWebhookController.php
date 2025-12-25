<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\Payment\MidtransService;
use App\Services\AdminReservasiService;
use App\Models\HomeCareReservasi;
use App\Models\HomeCareTracking;
use App\Models\Reservasi; 
use App\Models\DataPasien;
use App\Models\TransaksiBayar;

class MidtransWebhookController extends Controller
{
    protected $midtransService;
    protected $reservasiService;

    public function __construct(MidtransService $midtransService, AdminReservasiService $reservasiService)
    {
        $this->midtransService = $midtransService;
        $this->reservasiService = $reservasiService;
    }

    public function handle(Request $request)
    {

        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;

        Log::info(" Webhook Midtrans Masuk: {$orderId}", ['status' => $payload['transaction_status'] ?? '-']);

        if (!$orderId) {
            return response()->json(['message' => 'Invalid Payload'], 400);
        }

        // 1. Validasi Signature (Keamanan)
        if (
            !$this->midtransService->verifySignature(
                $orderId,
                $payload['status_code'],
                $payload['gross_amount'],
                $payload['signature_key']
            )
        ) {
            Log::error(" Invalid Signature Key untuk Order: $orderId");
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        // 2. Deteksi Tipe Transaksi & Cari Data di Database
        $transaksi = null;
        $tipeTransaksi = '';

        if (Str::startsWith($orderId, 'HC-')) {
            $transaksi = HomeCareReservasi::where('no_pemeriksaan', $orderId)->first();
            $tipeTransaksi = 'HOME_CARE';
        } elseif (Str::startsWith($orderId, 'RSV-')) {
            $transaksi = Reservasi::where('no_pemeriksaan', $orderId)->first();
            $tipeTransaksi = 'KLINIK';
        } elseif (Str::startsWith($orderId, 'PL-')) {
            if (preg_match('/^PL-(.+)-(\d+)$/', $orderId, $matches)) {
                $noPemeriksaanAsli = $matches[1];
                $transaksi = HomeCareReservasi::where('no_pemeriksaan', $noPemeriksaanAsli)->first();
                $tipeTransaksi = 'HOME_CARE_PELUNASAN';
            }
        }

        if (!$transaksi) {
            Log::error(" Order ID tidak ditemukan di database: $orderId");
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 3. Proses Update Status
        $transactionStatus = $payload['transaction_status'];

        DB::transaction(function () use ($transaksi, $transactionStatus, $tipeTransaksi, $payload) {
            $statusAwal = ($tipeTransaksi === 'HOME_CARE')
                ? $transaksi->status_booking
                : $transaksi->status_pembayaran;

            $keteranganLog = '';
            $poinDidapat = 0;

            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {

                if ($tipeTransaksi === 'HOME_CARE_PELUNASAN') {
                    $transaksi->status_pelunasan = 'lunas';
                    $transaksi->status = 'Selesai';
                    $keteranganLog = 'Pelunasan tagihan berhasil via Midtrans.';
                    $amountPaid = $payload['gross_amount'] ?? 0;
                    $poinDidapat = floor($amountPaid / 10000); 
                } else {
                    // Booking Awal (Klinik / HomeCare DP)
                    if ($tipeTransaksi === 'HOME_CARE') {
                        $transaksi->status_booking = 'lunas';
                        $transaksi->status = 'Menunggu Dokter';
                        $transaksi->status_reservasi = 'menunggu';
                    } else {
                        // KLINIK
                        $transaksi->status_pembayaran = 'lunas';
                        $transaksi->status_reservasi = 'menunggu'; 
                        $transaksi->status = 'Menunggu Dokter'; 
                        $this->reservasiService->processQueueLogic($transaksi, $transaksi->pasien_id, $transaksi->jadwal_id);
                    }

                    $keteranganLog = 'Pembayaran booking lunas via Midtrans.';
                    $amountPaid = $payload['gross_amount'] ?? 0;
                    $poinDidapat = floor($amountPaid / 10000);
                }

                // Tambah Poin User
                Log::info("🔍 [DEBUG POINT] Transaction ID: {$transaksi->no_pemeriksaan}, Pasien ID: {$transaksi->pasien_id}");
                if ($transaksi->pasien_id && $poinDidapat > 0) {
                    $affected = DB::table('users')
                        ->where('user_id', $transaksi->pasien_id)
                        ->increment('poin', $poinDidapat);
                    if ($affected) {
                        Log::info("🎁 [SUCCESS] User {$transaksi->pasien_id} mendapat {$poinDidapat} via DB Query.");
                    } else {
                        Log::info("❌ [ERROR] Failed to increment via DB Query. User ID not found: {$transaksi->pasien_id}");
                    }
                } else {
                    Log::info("⚠️ [WARNING] No User ID or 0 Points.");
                }

            } else if ($transactionStatus == 'expire' || $transactionStatus == 'cancel' || $transactionStatus == 'deny') {
                // PEMBAYARAN GAGAL
                if ($tipeTransaksi === 'HOME_CARE_PELUNASAN') {
                    $transaksi->status_pelunasan = 'gagal';
                    $keteranganLog = 'Pelunasan gagal/kadaluarsa.';
                } else {
                    if ($tipeTransaksi === 'HOME_CARE') {
                        $transaksi->status_booking = 'gagal';
                        $transaksi->status_reservasi = 'dibatalkan';
                        $transaksi->status = 'Dibatalkan';
                    } else {
                        // KLINIK
                        $transaksi->status_pembayaran = 'gagal';
                        $transaksi->status_reservasi = 'dibatalkan';
                        $transaksi->status = 'Dibatalkan';
                    }
                    $keteranganLog = 'Pembayaran booking gagal/kadaluarsa.';
                }

            } else if ($transactionStatus == 'pending') {
                // MENUNGGU PEMBAYARAN
                if ($tipeTransaksi === 'HOME_CARE_PELUNASAN') {
                    $keteranganLog = 'Menunggu pelunasan.';
                } else {
                    if ($tipeTransaksi === 'HOME_CARE') {
                        $transaksi->status_booking = 'belum_lunas';
                    } else {
                        $transaksi->status_pembayaran = 'menunggu_pembayaran';
                    }
                    $keteranganLog = 'Menunggu pembayaran booking.';
                }
            }

            // --- B. Simpan Perubahan jika ada update ---
            if ($transaksi->isDirty()) {
                $transaksi->save();
                $newStatus = ($tipeTransaksi === 'HOME_CARE') ? $transaksi->status_booking : $transaksi->status_pembayaran;
                Log::info("✅ Status {$tipeTransaksi} {$transaksi->no_pemeriksaan} diupdate menjadi: " . $newStatus);

                // --- C. Khusus Home Care: Catat Tracking ---
                if (($tipeTransaksi === 'HOME_CARE' || $tipeTransaksi === 'HOME_CARE_PELUNASAN') && !empty($keteranganLog)) {
                    $exists = HomeCareTracking::where('id_periksa', $transaksi->id)
                        ->where('keterangan', $keteranganLog)
                        ->exists();

                    if (!$exists) {
                        HomeCareTracking::create([
                            'id_periksa' => $transaksi->id,
                            'status_tracking' => 'assigned', 
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