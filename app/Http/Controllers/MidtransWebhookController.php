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

        // EMERGENCY DEBUG LOG
        file_put_contents(storage_path('logs/midtrans_debug.log'), date('Y-m-d H:i:s') . " - Payload: " . json_encode($payload) . PHP_EOL, FILE_APPEND);


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
            // --- LOGIC PELUNASAN HOME CARE ---
            // Format: PL-{NO_PEMERIKSAAN_ASLI}
            $noPemeriksaanAsli = substr($orderId, 3); // Remove 'PL-' prefix
            $transaksi = HomeCareReservasi::where('no_pemeriksaan', $noPemeriksaanAsli)->first();
            $tipeTransaksi = 'HOME_CARE_PELUNASAN';
        }

        if (!$transaksi) {
            Log::error(" Order ID tidak ditemukan di database: $orderId");
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 3. Proses Update Status
        $transactionStatus = $payload['transaction_status'];

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
                    $amountPaid = $payload['gross_amount'] ?? 0;
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
                    try {
                        // ROBUST LOGIC: Prioritas user_id (string), lalu id (integer)
                        $user = \App\Models\User::where('user_id', $transaksi->pasien_id)->first();
                        if (!$user) {
                            $user = \App\Models\User::where('id', $transaksi->pasien_id)->first();
                        }

                        if ($user) {
                             $user->increment('poin', $poinDidapat);
                             Log::info("🎁 [SUCCESS] User {$user->user_id} mendapat {$poinDidapat} poin.");
                             
                             // --- CATAT HISTORY POIN ---
                             try {
                                 \App\Models\PointHistory::create([
                                     'user_id' => $user->user_id,
                                     'amount' => $poinDidapat,
                                     'type' => 'earn',
                                     'description' => $keteranganLog,
                                     'reference_id' => $transaksi->no_pemeriksaan,
                                 ]);
                             } catch (\Exception $e) {
                                 Log::error("❌ Gagal mencatat history poin: " . $e->getMessage());
                             }
                        } else {
                            Log::error("❌ [ERROR] Failed to add points. User ID not found in DB: {$transaksi->pasien_id}");
                        }
                    } catch (\Exception $e) {
                        Log::error("❌ Critical Point Error: " . $e->getMessage());
                    }
                } else {
                    Log::info("⚠️ [WARNING] No User ID or 0 Points to add.");
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
                file_put_contents(storage_path('logs/midtrans_debug.log'), "  -> Saving transaction. Dirty: " . json_encode($transaksi->getDirty()) . PHP_EOL, FILE_APPEND);
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