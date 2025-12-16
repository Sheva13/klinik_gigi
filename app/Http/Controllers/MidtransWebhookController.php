<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon; // 🔥 Ditambahkan

// Import Service & Models
use App\Services\Payment\MidtransService;
use App\Models\HomeCareReservasi;
use App\Models\HomeCareTracking;
use App\Models\Reservasi; // Model Klinik
use App\Models\DataPasien; // 🔥 Ditambahkan
use App\Models\TransaksiBayar; // 🔥 Ditambahkan

class MidtransWebhookController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    // 🔥🔥 FUNGSI BARU: Logic Antrian & Transaksi Kasir (Hanya untuk Online Payment KLINIK) 🔥🔥
    private function processQueueLogic($reservasi) {
        $rmString = $reservasi->pasien_id;
        $jadwalId = $reservasi->jadwal_id;

        // 1. GENERATE NO ANTRIAN (Hanya jika belum ada)
        if (!$reservasi->no_antrian || $reservasi->no_antrian == '-') {
            $maxAntrian = DataPasien::where('id_jadwal', $jadwalId)->whereDate('created_at', Carbon::today())->max('no_antri');
            $urutanBaru = $maxAntrian ? ($maxAntrian + 1) : 1;

            $prefix = match($reservasi->jenis_pasien) { 'BPJS' => 'B', 'Asuransi' => 'A', default => 'U' };
            $reservasi->no_antrian = $prefix . '-' . str_pad($urutanBaru, 3, '0', STR_PAD_LEFT);
            $reservasi->save();
        } else {
            $parts = explode('-', $reservasi->no_antrian);
            $urutanBaru = (count($parts) > 1) ? (int) end($parts) : 1;
        }

        // 2. INSERT/UPDATE DATA PASIEN (Antrian Hari Ini)
        $cekAntrian = DataPasien::where('rekam_medis', $rmString)->where('id_jadwal', $jadwalId)->whereDate('created_at', Carbon::today())->first();
        $idPeriksa = null;

        if (!$cekAntrian) {
            $dp = DataPasien::create([
                'id_jadwal' => $jadwalId,
                'rekam_medis' => $rmString,
                'no_antri' => $urutanBaru,
                'status' => 1, 'pasien_baru' => 0, 'rujukan' => 0, 'biaya_admin' => 0, 'keluhan' => $reservasi->keluhan,
                'tanggal_periksa' => $reservasi->tanggal_pesan // Tambahkan tanggal periksa
            ]);
            $idPeriksa = $dp->id;
        } else {
            $idPeriksa = $cekAntrian->id;
        }

        // 3. INSERT TRANSAKSI BAYAR (Kasir)
        $cekTrx = TransaksiBayar::where('id_periksa', $idPeriksa)->first();
        if (!$cekTrx && $idPeriksa) {
            TransaksiBayar::create([
                'id_periksa' => $idPeriksa,
                'ambil_obat' => 0,
                'total_tindakan' => 0, 'total_obat' => 0, 'total_penunjang' => 0,
                'total_tambahan' => 0,
                'total_bayar' => $reservasi->pembayaran_total,
                'waktu' => Carbon::now(), 'diskon' => 0, 'biaya_admin' => 0, 'pasien_baru' => 0,
            ]);
        }
    }
    // 🔥🔥 END FUNGSI BARU 🔥🔥

    public function handle(Request $request)
    {

        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;

        Log::info("🔔 Webhook Midtrans Masuk: {$orderId}", ['status' => $payload['transaction_status'] ?? '-']);

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
            Log::error("❌ Invalid Signature Key untuk Order: $orderId");
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        // 2. Deteksi Tipe Transaksi & Cari Data di Database
        $transaksi = null;
        $tipeTransaksi = '';

        if (Str::startsWith($orderId, 'HC-')) {
            // --- LOGIC HOME CARE --- (TIDAK DIUBAH)
            $transaksi = HomeCareReservasi::where('no_pemeriksaan', $orderId)->first();
            $tipeTransaksi = 'HOME_CARE';
        } elseif (Str::startsWith($orderId, 'RSV-')) {
            // --- LOGIC KLINIK ---
            $transaksi = Reservasi::where('no_pemeriksaan', $orderId)->first();
            $tipeTransaksi = 'KLINIK';
        } elseif (Str::startsWith($orderId, 'PL-')) {
            // --- LOGIC PELUNASAN HOME CARE --- (TIDAK DIUBAH)
            if (preg_match('/^PL-(.+)-(\d+)$/', $orderId, $matches)) {
                $noPemeriksaanAsli = $matches[1];
                $transaksi = HomeCareReservasi::where('no_pemeriksaan', $noPemeriksaanAsli)->first();
                $tipeTransaksi = 'HOME_CARE_PELUNASAN';
            }
        }

        // Jika data tidak ditemukan di kedua tabel
        if (!$transaksi) {
            Log::error("❌ Order ID tidak ditemukan di database: $orderId");
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 3. Proses Update Status (Menggunakan DB Transaction)
        $transactionStatus = $payload['transaction_status'];

        DB::transaction(function () use ($transaksi, $transactionStatus, $tipeTransaksi, $payload) {
            $statusAwal = ($tipeTransaksi === 'HOME_CARE')
                ? $transaksi->status_booking
                : $transaksi->status_pembayaran;

            $keteranganLog = '';
            $poinDidapat = 0;

            // --- A. Logic Status Pembayaran ---
            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                // PEMBAYARAN BERHASIL
                
                if ($tipeTransaksi === 'HOME_CARE_PELUNASAN') {
                    // (LOGIC HOME CARE TIDAK DIUBAH)
                    $transaksi->status_pelunasan = 'lunas';
                    $transaksi->status = 'Selesai';
                    $keteranganLog = 'Pelunasan tagihan berhasil via Midtrans.';
                    $amountPaid = $payload['gross_amount'] ?? 0;
                    $poinDidapat = floor($amountPaid / 10000); 
                } else {
                    // Booking Awal (Klinik / HomeCare DP)
                    if ($tipeTransaksi === 'HOME_CARE') {
                        // (LOGIC HOME CARE TIDAK DIUBAH)
                        $transaksi->status_booking = 'lunas';
                        $transaksi->status = 'Menunggu Dokter';
                        $transaksi->status_reservasi = 'menunggu';
                    } else {
                        // KLINIK
                        $transaksi->status_pembayaran = 'lunas';
                        $transaksi->status_reservasi = 'menunggu'; 
                        $transaksi->status = 'Menunggu Dokter'; 
                        
                        // 🔥🔥 AKSI KRITIS KLINIK: MASUKKAN ANTRIAN DAN KASIR 🔥🔥
                        $this->processQueueLogic($transaksi);
                        // 🔥🔥 END AKSI KRITIS 🔥🔥
                    }

                    $keteranganLog = 'Pembayaran booking lunas via Midtrans.';
                    $amountPaid = $payload['gross_amount'] ?? 0;
                    $poinDidapat = floor($amountPaid / 10000);
                }

                // Tambah Poin User (TIDAK DIUBAH)
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
                        // KLINIK
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