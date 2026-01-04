<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\MidtransService;
use App\Services\ReservasiService;
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;

class TransaksiReservasiController extends ReservasiController
{
    protected $midtransService;
    protected $reservasiService;

    public function __construct(MidtransService $midtransService, ReservasiService $reservasiService)
    {
        $this->midtransService = $midtransService;
        $this->reservasiService = $reservasiService;
    }

    // ============================================================
    // 3. INTI TRANSAKSI (CREATE RESERVASI)
    // ============================================================
    public function createReservasi(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'rekam_medis_id'    => 'required|exists:rekam_medis,id',
                'dokter_id'         => 'required|string|exists:master_dokter,kode_dokter',
                'jadwal_id'         => 'required|integer|exists:master_jadwal,id',
                'tanggal_pesan'     => 'required|date_format:Y-m-d',
                'keluhan'           => 'nullable|string|max:100',
                'metode_pembayaran' => 'required|string|in:Midtrans,Umum',
                'jenis_pasien'      => 'required|string|in:Umum,BPJS,Asuransi',
                'user_name'         => 'required|string',
                'user_email'        => 'required|email',
                'user_phone'        => 'required|string',
            ]);

            $pasien = RekamMedis::find($validated['rekam_medis_id']);
            $dokter = MasterDokter::where('kode_dokter', $validated['dokter_id'])->firstOrFail();
            $jadwal = MasterJadwal::find($validated['jadwal_id']);

            // Validasi Ketersediaan (Hari & Libur) - menggunakan service
            $this->reservasiService->cekKetersediaanJadwal($validated['jadwal_id'], $validated['tanggal_pesan']);

            // Logic Reservasi - menggunakan service untuk generate nomor
            $noPemeriksaan = $this->reservasiService->generateNoPemeriksaan();
            $biaya = ($validated['metode_pembayaran'] === 'Midtrans') ? 25000 : 0;
            $statusPembayaranAwal = ($validated['metode_pembayaran'] === 'Midtrans') ? 'menunggu_pembayaran' : 'menunggu_verifikasi';

            $reservasi = $this->reservasiService->simpanReservasi([
                'no_pemeriksaan'    => $noPemeriksaan,
                'pasien_id'         => $pasien->rekam_medis,
                'dokter_id'         => $dokter->kode_dokter,
                'jadwal_id'         => $jadwal->id,
                'tanggal_pesan'     => $validated['tanggal_pesan'],
                'waktu_pesan'       => Carbon::now()->format('H:i:s'),
                'jam_mulai'         => $jadwal->jam_mulai,
                'jam_selesai'       => $jadwal->jam_selesai,
                'keluhan'           => $validated['keluhan'] ?? '-',
                'biaya_reservasi'   => $biaya,
                'pembayaran_total'  => $biaya,
                'status'            => 'Menunggu Pembayaran',
                'status_reservasi'  => 'menunggu',
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => $statusPembayaranAwal,
                'jenis_pasien'      => $validated['jenis_pasien'],
            ]);

            // Logic Midtrans (Snap URL)
            $redirectUrl = null;

            if ($validated['metode_pembayaran'] === 'Midtrans') {
                $redirectUrl = $this->midtransService->getSnapUrl(
                    $reservasi->no_pemeriksaan,
                    $reservasi->pembayaran_total,
                    $validated['user_name'],
                    $validated['user_email'],
                    $validated['user_phone'],
                    [[
                        'id' => 'RES-'.$reservasi->id,
                        'price' => $reservasi->pembayaran_total,
                        'quantity' => 1,
                        'name' => 'Biaya Reservasi Dokter'
                    ]]
                );
            }

            DB::commit();

            return $this->successResponse('Reservasi berhasil dibuat.', [
                'no_pemeriksaan'    => $reservasi->no_pemeriksaan,
                'total_bayar'       => number_format($reservasi->pembayaran_total, 0, ',', '.'),
                'redirect_url'      => $redirectUrl, // Mengirim URL ke Flutter
                'metode_pembayaran' => $reservasi->metode_pembayaran,
                'status_pembayaran' => $reservasi->status_pembayaran,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Create Reservasi Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memproses reservasi: ' . $e->getMessage(), null, 500);
        }
    }

    // ============================================================
    // 4. CEK STATUS PEMBAYARAN (POLLING)
    // ============================================================
    public function cekStatusPembayaran($no_pemeriksaan)
    {
        try {
            $reservasi = Reservasi::where('no_pemeriksaan', $no_pemeriksaan)->first();
            if (!$reservasi) return $this->errorResponse('Order ID tidak ditemukan', null, 404);

            $status = $reservasi->status_pembayaran;

            return $this->successResponse('Status pembayaran berhasil diambil', [
                'no_pemeriksaan'    => $no_pemeriksaan,
                'status_pembayaran' => $status,
                'is_lunas'          => ($status === 'lunas' || $status === 'terverifikasi'),
                'is_pending'        => ($status === 'menunggu_pembayaran'),
                'is_failed'         => ($status === 'gagal'),
                'detail_status'     => $reservasi->status,
                'total_bayar'       => number_format($reservasi->pembayaran_total, 0, ',', '.'),
                'no_antrian'        => $reservasi->no_antrian ?? '-',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal mengambil status', null, 500);
        }
    }

    // ============================================================
    // 5. UPDATE PEMBAYARAN (MANUAL / FALLBACK)
    // ============================================================
    public function updatePembayaran(Request $request, $no_pemeriksaan) {
        DB::beginTransaction();
        try {
            $reservasi = Reservasi::where('no_pemeriksaan', $no_pemeriksaan)->first();
            if (!$reservasi) return $this->errorResponse('Tidak ditemukan', null, 404);
            if ($reservasi->status_pembayaran === 'menunggu_pembayaran') {
                $reservasi->update([
                    'status_pembayaran' => 'lunas',
                    'status_reservasi'  => 'terkonfirmasi',
                    'status'            => 'Menunggu Dokter',
                ]);
            }

            DB::commit();
            return $this->successResponse('Pembayaran Valid', ['reservasi' => $reservasi]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal update', $e->getMessage(), 500);
        }
    }
}