<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterPoli;
use App\Models\JadwalHarian;
use App\Services\AdminReservasiService; // Panggil Service
use Illuminate\Support\Facades\DB;
use Exception;

class AdminReservasiController extends Controller
{
    protected $reservasiService;

    public function __construct(AdminReservasiService $reservasiService)
    {
        $this->reservasiService = $reservasiService;
    }

    // --- Helper Response ---
    protected function success($msg, $data = null) {
        return response()->json(['status' => 'success', 'message' => $msg, 'data' => $data]);
    }
    protected function error($msg, $code = 400) {
        return response()->json(['status' => 'error', 'message' => $msg], $code);
    }

    public function index(Request $request)
    {
        $query = Reservasi::with(['rekamMedis:rekam_medis,nama', 'dokter', 'dokter.masterPoli', 'jadwal:id,hari,jam_mulai,jam_selesai']); 

        // Filter Logic
        if ($request->no_rm) {
            $query->whereHas('rekamMedis', fn($q) => $q->where('rekam_medis', 'LIKE', "%{$request->no_rm}%")->orWhere('nama', 'LIKE', "%{$request->no_rm}%"));
        }
        if ($request->poli_id && $request->poli_id !== "semua") $query->whereHas('dokter.masterPoli', fn($q) => $q->where('kode_poli', $request->poli_id));
        if ($request->dokter_id && $request->dokter_id !== "semua") $query->where('dokter_id', $request->dokter_id);
        if ($request->status_pembayaran && $request->status_pembayaran !== "semua") $query->where('status_pembayaran', $request->status_pembayaran);
        if ($request->status_reservasi && $request->status_reservasi !== "semua") $query->where('status_reservasi', $request->status_reservasi);

        // Stats Counters
        $stats = [
            'total'    => (clone $query)->count(),
            'menunggu' => (clone $query)->where('status_reservasi', 'menunggu')->count(),
            'diproses' => (clone $query)->where('status_reservasi', 'dalam_proses')->count(), 
            'selesai'  => (clone $query)->where('status_reservasi', 'selesai')->count(),
            'batal'    => (clone $query)->where('status_reservasi', 'batal')->count(),
        ];

        $data = $query->orderBy('tanggal_pesan', 'desc')->orderBy('waktu_pesan', 'desc')->paginate(10);
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get();
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();

        return view('reservasi.index', compact('data', 'stats', 'dokters', 'polis'));
    }

    public function create()
    {
        $pasiens = RekamMedis::select('id', 'nama', 'rekam_medis')->get(); 
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get(); 
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();
        $jadwals = JadwalHarian::all(); 
        return view('reservasi.create', compact('pasiens', 'dokters', 'polis', 'jadwals')); 
    }

    // Endpoint Action manual
    public function createManual(Request $request)
    {
        $request->validate([
            'nama_lengkap'  => 'required_without:pasien_id_exist|string|max:255', 
            'poli'          => 'required|exists:master_poli,kode_poli', 
            'dokter'        => 'required|exists:master_dokter,kode_dokter',
            'tanggal_janji' => 'required|date',
            'waktu_janji'   => 'required',
            'jenis_pasien'  => 'required|string|in:Umum,BPJS,Asuransi',
        ]);

        DB::beginTransaction();
        try {
            $reservasi = $this->reservasiService->handleCreateManual($request->all());
            
            DB::commit();
            $pasienNama = $reservasi->rekamMedis ? $reservasi->rekamMedis->nama : 'Pasien';
            return $this->success('Reservasi berhasil dibuat untuk ' . $pasienNama);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Gagal: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $reservasi = Reservasi::with(['rekamMedis', 'dokter', 'jadwal.poli'])->findOrFail($id);
        $dokters = MasterDokter::all(); 
        return view('reservasi.show', compact('reservasi', 'dokters'));
    }

    public function edit($id)
    {
        $reservasi = Reservasi::with(['rekamMedis', 'dokter', 'jadwal'])->find($id);
        if (!$reservasi) return redirect()->route('reservasi.admin.index')->with('error', 'Data tidak ditemukan.');
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get();
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();
        return view('reservasi.edit', compact('reservasi', 'dokters', 'polis')); 
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->reservasiService->handleUpdate($id, $request->all());
            DB::commit();
            return back()->with('success', 'Data reservasi berhasil diperbarui.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
    
    // Cari Pasien 
    public function cariPasien(Request $request)
    {
        if ($request->has('q')) {
            $data = RekamMedis::where('rekam_medis', 'LIKE', "%{$request->q}%")
                    ->orWhere('nama', 'LIKE', "%{$request->q}%")
                    ->limit(5)->get();

            return response()->json($data->map(function($item) {
                return [
                    'id_database'     => $item->id,
                    'nomor_rm'        => $item->rekam_medis,
                    'nama_lengkap'    => $item->nama,
                    'label_pencarian' => $item->rekam_medis . ' - ' . $item->nama,
                ];
            }));
        }
        return response()->json([]);
    }

    // Update status pembayaran
    public function updatePembayaran(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:menunggu_pembayaran,lunas,terverifikasi,gagal',
            'metode_pembayaran' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $reservasi = Reservasi::findOrFail($id);
            $oldStatus = $reservasi->status_pembayaran;

            $reservasi->status_pembayaran = $request->status_pembayaran;

            if ($request->filled('metode_pembayaran')) {
                $reservasi->metode_pembayaran = $request->metode_pembayaran;
            }

            // Jika status pembayaran menjadi verified atau lunas masuk ke antrian
            if (in_array($request->status_pembayaran, ['lunas', 'terverifikasi'])) {
                if ($reservasi->status_reservasi === 'menunggu_pembayaran') {
                    $reservasi->status_reservasi = 'menunggu';
                }
                $rmString = $reservasi->rekamMedis ? $reservasi->rekamMedis->rekam_medis : $reservasi->pasien_id;
                $this->reservasiService->processQueueLogic($reservasi, $rmString, $reservasi->jadwal_id);
            }

            $reservasi->save();

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil diperbarui');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update pembayaran: ' . $e->getMessage());
        }
    }

    // Update status reservasi
    public function updateStatusReservasi(Request $request, $id)
    {
        $request->validate([
            'status_reservasi' => 'required|in:menunggu,dalam_proses,selesai,batal',
        ]);

        DB::beginTransaction();
        try {
            $reservasi = Reservasi::findOrFail($id);
            $oldStatus = $reservasi->status_reservasi;

            $reservasi->status_reservasi = $request->status_reservasi;
            $reservasi->save();

            DB::commit();
            return back()->with('success', 'Status reservasi berhasil diperbarui');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update status reservasi: ' . $e->getMessage());
        }
    }
}