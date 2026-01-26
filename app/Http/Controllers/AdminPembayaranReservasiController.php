<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterPoli;
use App\Models\JadwalHarian;
use App\Services\AdminReservasiService;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminPembayaranReservasiController extends Controller
{
    protected $reservasiService;

    public function __construct(AdminReservasiService $reservasiService)
    {
        $this->reservasiService = $reservasiService;
    }

    // Menampilkan halaman detail pembayaran
    public function showPayment($id)
    {
        $reservasi = Reservasi::with(['rekamMedis', 'dokter', 'jadwal'])->findOrFail($id);
        return view('reservasi.payment-detail', compact('reservasi'));
    }

    // Menandai pembayaran sebagai lunas
    public function tandaiLunas(Request $request, $id)
    {
        $reservasi = Reservasi::with('rekamMedis')->findOrFail($id);

        DB::beginTransaction();
        try {
            if ($request->hasFile('bukti_pembayaran')) {
                $path = $request->file('bukti_pembayaran')->store('bukti_bayar', 'public');
                $reservasi->bukti_pembayaran_path = $path;
                $reservasi->bukti_pembayaran_file_name = $request->file('bukti_pembayaran')->getClientOriginalName();
            }

            $reservasi->status_pembayaran = 'terverifikasi';
            $reservasi->status_reservasi = 'menunggu';

            $rmString = $reservasi->rekamMedis ? $reservasi->rekamMedis->rekam_medis : $reservasi->pasien_id;
            $this->reservasiService->processQueueLogic($reservasi, $rmString, $reservasi->jadwal_id);

            DB::commit();
            return redirect()->route('reservasi.admin.index')->with('success', 'LUNAS! Pasien Masuk Antrian.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
    
    // Fungsi untuk menangani verifikasi pembayaran manual
    public function verifyPayment(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:terverifikasi,gagal',
        ]);

        DB::beginTransaction();
        try {
            $reservasi = Reservasi::findOrFail($id);
            $oldStatus = $reservasi->status_pembayaran;
            
            $reservasi->status_pembayaran = $request->status_pembayaran;
            
            // Jika pembayaran terverifikasi, atur status reservasi dan masukkan antrian
            if ($request->status_pembayaran === 'terverifikasi') {
                $reservasi->status_reservasi = 'menunggu';
                
                // Proses masuk antrian
                $this->reservasiService->processQueueLogic($reservasi, $reservasi->pasien_id, $reservasi->jadwal_id);
            }
            
            $reservasi->save();

            DB::commit();
            
            return back()->with('success', 'Pembayaran berhasil diverifikasi');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Verifikasi pembayaran gagal: ' . $e->getMessage());
        }
    }
}