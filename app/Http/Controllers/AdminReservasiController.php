<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterPoli;
use App\Models\Jadwal;
use Carbon\Carbon;
use Exception;

class AdminReservasiController extends Controller
{
        // Helper JSON Response
    protected function success($msg, $data = null)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $msg,
            'data'    => $data
        ]);
    }

    protected function error($msg, $code = 400)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $msg
        ], $code);
    }

    // List Reservasi (Multi-Filter Ready)
    public function index(Request $request)
    {
        // 1. Start Query & Load Relasi
        $query = Reservasi::with([
            'rekamMedis:rekam_medis,nama', 
            'dokter.masterPoli:kode_poli,nama_poli', 
            'dokter:kode_dokter,nama,kode_poli', 
            'jadwal:id,hari,jam_mulai,jam_selesai'
        ]); 

        // 2. Filter 1: No RM / Nama Pasien
        if ($request->no_rm) {
            $query->whereHas('rekamMedis', function ($q) use ($request) {
                $q->where('rekam_medis', 'LIKE', "%{$request->no_rm}%")
                  ->orWhere('nama', 'LIKE', "%{$request->no_rm}%");
            });
        }

        // 3. Filter 2: Poli
        if ($request->poli_id && $request->poli_id !== "semua") {
            $query->whereHas('dokter.masterPoli', function ($q) use ($request) {
                $q->where('kode_poli', $request->poli_id); 
            });
        }

        // 4. Filter 3: Dokter
        if ($request->dokter_id && $request->dokter_id !== "semua") {
            $query->where('dokter_id', $request->dokter_id);
        }

        // 5. Filter 4: Status Pembayaran
        if ($request->status_pembayaran && $request->status_pembayaran !== "semua") {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        // 6. Filter 5: Status Reservasi
        if ($request->status_reservasi && $request->status_reservasi !== "semua") {
            $query->where('status_reservasi', $request->status_reservasi);
        }

        // Hitung Statistik (Sesuai ENUM DB)
        $statsQuery = clone $query; 

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'menunggu' => (clone $statsQuery)->whereIn('status_reservasi', ['menunggu'])->count(),
            'selesai'  => (clone $statsQuery)->whereIn('status_reservasi', ['selesai'])->count(),
            'batal'    => (clone $statsQuery)->whereIn('status_reservasi', ['batal'])->count(),
        ];

        // 7. Ambil Data Akhir (Paginate)
        $data = $query
            ->orderBy('tanggal_pesan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Ambil data MasterDokter & MasterPoli untuk filter dropdown
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get();
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();

        return view('reservasi.index', compact('data', 'stats', 'dokters', 'polis'));
    }

    // Halaman Form Create
    public function create()
    {
        $pasiens = RekamMedis::select('rekam_medis', 'nama')->get();
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get(); 
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();
        $jadwals = Jadwal::all(); 

        return view('reservasi.create', compact('pasiens', 'dokters', 'polis', 'jadwals')); 
    }

    // Detail Reservasi
    public function show($id)
    {
        $reservasi = Reservasi::with([
            'rekamMedis', 'dokter', 'dokter.masterPoli', 'jadwal'
        ])->find($id);
        
        if (!$reservasi) {
            return redirect()->route('reservasi.admin.index')->with('error', 'Reservasi tidak ditemukan');
        }

        return view('reservasi.show', compact('reservasi'));
    }

    // Generate Nomor Pemeriksaan
    private function generateNoPemeriksaan()
    {
        $tanggal = Carbon::now()->format('Ymd');
        do {
            $random = random_int(100, 999);
            $no = "RSV-{$tanggal}{$random}";
        } while (Reservasi::where('no_pemeriksaan', $no)->exists());
        return $no;
    }

    // Update Status Pembayaran
    public function updatePembayaran(Request $request, $id)
    {
        $request->validate(['status_pembayaran' => 'required']);

        $reservasi = Reservasi::find($id);
        if (!$reservasi) return $this->error('Reservasi tidak ditemukan', 404);

        $newStatus = $request->status_pembayaran;

        // Jika status lunas (terverifikasi), generate nomor antrian
        if ($newStatus === 'terverifikasi' && !$reservasi->no_pemeriksaan) {
            $reservasi->no_pemeriksaan = $this->generateNoPemeriksaan();
        }

        // Jika pembayaran gagal/batal, status reservasi ikut batal
        if ($newStatus === 'gagal') {
            $reservasi->status_reservasi = 'batal';
        }

        $reservasi->status_pembayaran = $newStatus;
        $reservasi->save();

        return $this->success('Status pembayaran diperbarui', $reservasi);
    }

    // Update Status Reservasi
    public function updateStatusReservasi(Request $request, $id)
    {
        $request->validate(['status_reservasi' => 'required']);

        $reservasi = Reservasi::find($id);
        if (!$reservasi) return $this->error('Reservasi tidak ditemukan', 404);

        $newStatus = $request->status_reservasi;

        // Auto update pembayaran jika status 'dalam_proses'
        if ($newStatus === 'dalam_proses') {
            // Jika pembayaran masih menunggu, anggap lunas karena sudah diproses dokter
            if ($reservasi->status_pembayaran === 'menunggu_pembayaran' || $reservasi->status_pembayaran === 'menunggu_verifikasi') {
                $reservasi->status_pembayaran = 'terverifikasi';
            }
            if (!$reservasi->no_pemeriksaan) {
                $reservasi->no_pemeriksaan = $this->generateNoPemeriksaan();
            }
        }

        if ($newStatus === 'selesai') {
            $reservasi->status_pembayaran = 'terverifikasi';
        }

        if ($newStatus === 'batal') {
            $reservasi->status_pembayaran = 'gagal';
        }

        $reservasi->status_reservasi = $newStatus;
        $reservasi->save();

        return $this->success('Status reservasi diperbarui', $reservasi);
    }

    // Simpan Reservasi Manual
    public function createManual(Request $request)
    {
        $request->validate([
            'pasien_id'     => 'required|exists:rekam_medis,rekam_medis', 
            'dokter_id'     => 'required|exists:master_dokter,kode_dokter', 
            'poli_id'       => 'required|exists:master_poli,kode_poli', 
            'jadwal_id'     => 'required',
            'tanggal_pesan' => 'required|date'
        ]);

        DB::beginTransaction();
        try {
            $reservasi = Reservasi::create([
                'pasien_id'         => $request->pasien_id,
                'dokter_id'         => $request->dokter_id,
                'jadwal_id'         => $request->jadwal_id,
                'tanggal_pesan'     => $request->tanggal_pesan,
                // Default value sesuai DB
                'status_pembayaran' => 'terverifikasi', // Admin input manual dianggap lunas
                'status_reservasi'  => 'menunggu',
                'no_pemeriksaan'    => $this->generateNoPemeriksaan()
            ]);

            DB::commit();
            return $this->success('Reservasi manual berhasil dibuat', $reservasi);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Gagal membuat reservasi: ' . $e->getMessage(), 500);
        }
    }
}