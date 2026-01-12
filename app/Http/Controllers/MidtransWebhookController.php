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
        try {
            $payload = $request->all();
            $orderId = $payload['order_id'] ?? null;

            Log::info(" Webhook Midtrans Masuk: {$orderId}", ['status' => $payload['transaction_status'] ?? '-']);

            // EMERGENCY DEBUG LOG
            file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - Payload: " . json_encode($payload) . PHP_EOL, FILE_APPEND);


            if (!$orderId) {
                return response()->json(['message' => 'Invalid Payload'], 400);
            }

            // 1. Validasi Signature (Keamanan)
            // Normalize payload values to avoid TypeErrors when keys are missing
            $statusCode = (string) ($payload['status_code'] ?? '');
            $grossAmount = (string) ($payload['gross_amount'] ?? $payload['gross_amount_str'] ?? '0');
            $signatureKey = (string) ($payload['signature_key'] ?? $payload['signature'] ?? '');

            if (
                !$this->midtransService->verifySignature(
                    $orderId,
                    $statusCode,
                    $grossAmount,
                    $signatureKey
                )
            ) {
                Log::error(" Invalid Signature Key untuk Order: $orderId");
                file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - ❌ SIGNATURE INVALID for {$orderId} | status_code={$statusCode} gross_amount={$grossAmount} signature_present=" . (!empty($signatureKey) ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);
                return response()->json(['message' => 'Invalid Signature'], 403);
            }
            file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - ✅ SIGNATURE VALID" . PHP_EOL, FILE_APPEND);

            // 2. Deteksi Tipe Transaksi & Cari Data di Database
            $transaksi = null;
            $tipeTransaksi = '';

            if (Str::startsWith($orderId, 'HC-')) {
                $transaksi = HomeCareReservasi::where('no_pemeriksaan', $orderId)->first();
                $tipeTransaksi = 'HOME_CARE';
                file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - 🏠 DETECTED: HOME_CARE ($orderId)" . PHP_EOL, FILE_APPEND);
            } elseif (Str::startsWith($orderId, 'RSV-')) {
                $transaksi = Reservasi::where('no_pemeriksaan', $orderId)->first();
                $tipeTransaksi = 'KLINIK';
                file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - 🏥 DETECTED: KLINIK ($orderId)" . PHP_EOL, FILE_APPEND);
            } elseif (Str::startsWith($orderId, 'PL-')) {
                // --- LOGIC PELUNASAN HOME CARE ---
                // Format: PL-{NO_PEMERIKSAAN_ASLI}
                $noPemeriksaanAsli = substr($orderId, 3); // Remove 'PL-' prefix
                $transaksi = HomeCareReservasi::where('no_pemeriksaan', $noPemeriksaanAsli)->first();
                $tipeTransaksi = 'HOME_CARE_PELUNASAN';
                file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - 💳 DETECTED: HOME_CARE_PELUNASAN ($orderId → $noPemeriksaanAsli)" . PHP_EOL, FILE_APPEND);
            }

            if (!$transaksi) {
                Log::error(" Order ID tidak ditemukan di database: $orderId");
                file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - ❌ TRANSAKSI NOT FOUND: $orderId" . PHP_EOL, FILE_APPEND);
                return response()->json(['message' => 'Order not found'], 404);
            }
            file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - ✅ TRANSAKSI FOUND: {$transaksi->no_pemeriksaan}" . PHP_EOL, FILE_APPEND);

            // 3. Proses Update Status
            $transactionStatus = $payload['transaction_status'] ?? null;
            if (!$transactionStatus) {
                file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - ❌ Missing transaction_status in payload for {$orderId}" . PHP_EOL, FILE_APPEND);
                return response()->json(['message' => 'Invalid Payload: missing transaction_status'], 400);
            }

            file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - 🔄 TRANSACTION STATUS: $transactionStatus" . PHP_EOL, FILE_APPEND);

            DB::transaction(function () use ($transaksi, $transactionStatus, $tipeTransaksi, $payload) {
            $statusAwal = ($tipeTransaksi === 'HOME_CARE')
                ? $transaksi->status_pembayaran
                : $transaksi->status_pembayaran;

            $keteranganLog = '';
            $poinDidapat = 0;

            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {

                if ($tipeTransaksi === 'HOME_CARE_PELUNASAN') {
                    $transaksi->status_pelunasan = 'lunas';
                    $transaksi->status = 'Selesai';
                    $keteranganLog = 'Pelunasan tagihan berhasil via Midtrans.';
                    $amountPaid = (int) round(floatval($payload['gross_amount'] ?? 0));
                    $poinDidapat = floor($amountPaid / 10000); 
                } else {
                    // Booking Awal (Klinik / HomeCare DP)
                    if ($tipeTransaksi === 'HOME_CARE') {
                        $transaksi->status_booking = 'lunas';
                        $transaksi->status = 'Menunggu Dokter';
                        $transaksi->status_reservasi = 'menunggu';
                        file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> Logic settlement HOME_CARE executed for {$transaksi->no_pemeriksaan}" . PHP_EOL, FILE_APPEND);
                    } else {
                        // KLINIK
                        // Use enum-compliant value for reservasi.status_pembayaran
                        $transaksi->status_pembayaran = 'terverifikasi';
                        $transaksi->status_reservasi = 'menunggu'; 
                        $transaksi->status = 'Menunggu Dokter'; 
                        $this->reservasiService->processQueueLogic($transaksi, $transaksi->pasien_id, $transaksi->jadwal_id);
                    }

                    $keteranganLog = 'Pembayaran booking terverifikasi via Midtrans.';
                    $amountPaid = (int) round(floatval($payload['gross_amount'] ?? 0));
                    $poinDidapat = floor($amountPaid / 10000);
                }

                // Tambah Poin User - WITH DUPLICATE CHECK
                Log::info("🔍 [DEBUG POINT] Transaction ID: {$transaksi->no_pemeriksaan}, Pasien ID: {$transaksi->pasien_id}");
                file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> 🔍 POINTS LOGIC: pasien_id={$transaksi->pasien_id}, poinDidapat={$poinDidapat}" . PHP_EOL, FILE_APPEND);
                if ($transaksi->pasien_id && $poinDidapat > 0) {
                    // Check if points already added for this transaction (use type + reference_id for reliable check)
                    $historyExists = \App\Models\PointHistory::where('reference_id', $transaksi->no_pemeriksaan)
                                        ->where('type', 'earn')
                                        ->exists();
                    
                    if (!$historyExists) {
                        try {
                            // ROBUST LOGIC: Prioritas user_id (string), lalu id (integer)
                            $user = \App\Models\User::where('user_id', $transaksi->pasien_id)->first();
                            if (!$user) {
                                $user = \App\Models\User::where('id', $transaksi->pasien_id)->first();
                            }

                            if ($user) {
                                 $user->increment('poin', $poinDidapat);
                                 Log::info("🎁 [SUCCESS] User {$user->user_id} mendapat {$poinDidapat} poin.");
                                 file_put_contents(storage_path('logs/midtrans_debug.log'), "    ✅ POINTS ADDED: {$poinDidapat} to user {$user->user_id}" . PHP_EOL, FILE_APPEND);
                                 
                                 // --- CATAT HISTORY POIN ---
                                 try {
                                     \App\Models\PointHistory::create([
                                         'user_id' => $user->user_id,
                                         'amount' => $poinDidapat,
                                         'type' => 'earn',
                                         'description' => $keteranganLog,
                                         'reference_id' => $transaksi->no_pemeriksaan,
                                     ]);
                                     file_put_contents(storage_path('logs/midtrans_debug.log'), "    ✅ POINT HISTORY RECORDED" . PHP_EOL, FILE_APPEND);
                                 } catch (\Exception $e) {
                                     Log::error("❌ Gagal mencatat history poin: " . $e->getMessage());
                                     file_put_contents(storage_path('logs/midtrans_debug.log'), "    ❌ POINT HISTORY FAILED: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
                                 }
                            } else {
                                Log::error("❌ [ERROR] Failed to add points. User ID not found in DB: {$transaksi->pasien_id}");
                                file_put_contents(storage_path('logs/midtrans_debug.log'), "    ❌ USER NOT FOUND: {$transaksi->pasien_id}" . PHP_EOL, FILE_APPEND);
                            }
                        } catch (\Exception $e) {
                            Log::error("❌ Critical Point Error: " . $e->getMessage());
                            file_put_contents(storage_path('logs/midtrans_debug.log'), "    ❌ POINTS ERROR: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
                        }
                    } else {
                        Log::info("⚠️ [DUPLICATE] Points already added for {$transaksi->no_pemeriksaan}, skipping.");
                        file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> ⚠️ POINTS ALREADY ADDED (duplicate prevented)" . PHP_EOL, FILE_APPEND);
                    }
                } else {
                    Log::info("⚠️ [WARNING] No User ID or 0 Points to add.");
                    file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> ⚠️ POINTS SKIPPED: pasien_id empty or poinDidapat=0" . PHP_EOL, FILE_APPEND);
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
                $dirtyFields = $transaksi->getDirty();
                file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> Saving transaction. Dirty: " . json_encode($dirtyFields) . PHP_EOL, FILE_APPEND);
                try {
                    $transaksi->save();
                    file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> ✅ SAVE SUCCESSFUL. Updated fields: " . json_encode($dirtyFields) . PHP_EOL, FILE_APPEND);
                } catch (\Exception $saveError) {
                    file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> ❌ SAVE FAILED: " . $saveError->getMessage() . " | Trace: " . $saveError->getTraceAsString() . PHP_EOL, FILE_APPEND);
                    throw $saveError; // Re-throw to trigger catch block
                }
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
            } else {
                file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> ⚠️ NO CHANGES DETECTED (isDirty=false)" . PHP_EOL, FILE_APPEND);
            }
        });

        file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - ✅✅✅ WEBHOOK COMPLETED SUCCESSFULLY ✅✅✅" . PHP_EOL . PHP_EOL, FILE_APPEND);
        return response()->json(['message' => 'Webhook processed successfully']);
        } catch (\Illuminate\Database\QueryException $dbError) {
            // Database specific errors
            Log::error("❌ Database Error: " . $dbError->getMessage());
            file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - ❌ DATABASE ERROR: " . $dbError->getMessage() . " | SQL: " . $dbError->getSql() . " | Bindings: " . json_encode($dbError->getBindings()) . " | Full Trace: " . $dbError->getTraceAsString() . PHP_EOL, FILE_APPEND);
            return response()->json(['message' => 'Database error: ' . $dbError->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error("❌ Webhook Error: " . $e->getMessage());
            Log::error("Stack: " . $e->getTraceAsString());
            file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - ❌ ERROR: " . $e->getMessage() . " | Class: " . get_class($e) . " | Trace: " . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
            return response()->json(['message' => 'Error processing webhook: ' . $e->getMessage()], 500);
        }
    }
}