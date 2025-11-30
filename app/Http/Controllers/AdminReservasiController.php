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
    /**
     * Helper: Response Sukses JSON
     *
     * @param string $msg
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($msg, $data = null)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $msg,
            'data'    => $data
        ]);
    }

    /**
     * Helper: Response Error JSON
     *
     * @param string $msg
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($msg, $code = 400)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $msg
        ], $code);
    }

    /**
     * Menampilkan daftar Reservasi dengan fitur Multi-Filter.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 1. Mulai Query & Load Relasi yang dibutuhkan (Eager Loading)
        $query = Reservasi::with([
            'rekamMedis:rekam_medis,nama', 
            'dokter.masterPoli:kode_poli,nama_poli', 
            'dokter:kode_dokter,nama,kode_poli', 
            'jadwal:id,hari,jam_mulai,jam_selesai'
        ]); 

        // 2. Filter berdasarkan No. RM atau Nama Pasien
        if ($request->no_rm) {
            $query->whereHas('rekamMedis', function ($q) use ($request) {
                $q->where('rekam_medis', 'LIKE', "%{$request->no_rm}%")
                  ->orWhere('nama', 'LIKE', "%{$request->no_rm}%");
            });
        }

        // 3. Filter berdasarkan Poli
        if ($request->poli_id && $request->poli_id !== "semua") {
            $query->whereHas('dokter.masterPoli', function ($q) use ($request) {
                $q->where('kode_poli', $request->poli_id); 
            });
        }

        // 4. Filter berdasarkan Dokter
        if ($request->dokter_id && $request->dokter_id !== "semua") {
            $query->where('dokter_id', $request->dokter_id);
        }

        // 5. Filter berdasarkan Status Pembayaran
        if ($request->status_pembayaran && $request->status_pembayaran !== "semua") {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        // 6. Filter berdasarkan Status Reservasi
        if ($request->status_reservasi && $request->status_reservasi !== "semua") {
            $query->where('status_reservasi', $request->status_reservasi);
        }

        // Hitung Statistik (berdasarkan filter yang aktif)
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

    /**
     * Menampilkan halaman form untuk membuat Reservasi Manual.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Ambil data master untuk dropdown form
        $pasiens = RekamMedis::select('rekam_medis', 'nama')->get();
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get(); 
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();
        $jadwals = Jadwal::all(); 

        return view('reservasi.create', compact('pasiens', 'dokters', 'polis', 'jadwals')); 
    }

    /**
     * Menampilkan detail Reservasi.
     * (Saat ini dialihkan ke form edit sesuai catatan Lixa)
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $reservasi = Reservasi::with([
            'rekamMedis', 'dokter', 'dokter.masterPoli', 'jadwal'
        ])->find($id);
        
        if (!$reservasi) {
            return redirect()->route('reservasi.admin.index')->with('error', 'Reservasi tidak ditemukan');
        }

        // ⚠️ CATATAN LIXA: Menggunakan 'reservasi.edit' untuk sementara (sesuai kode asli)
        return view('reservasi.edit', compact('reservasi')); 
    }

    /**
     * Generate Nomor Pemeriksaan unik berdasarkan tanggal dan random number.
     *
     * @return string
     */
    private function generateNoPemeriksaan()
    {
        $tanggal = Carbon::now()->format('Ymd');
        do {
            $random = random_int(100, 999);
            $no = "RSV-{$tanggal}{$random}";
        } while (Reservasi::where('no_pemeriksaan', $no)->exists());
        return $no;
    }

    /**
     * Update Status Pembayaran Reservasi.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePembayaran(Request $request, $id)
    {
        $request->validate(['status_pembayaran' => 'required']);

        $reservasi = Reservasi::find($id);
        if (!$reservasi) return $this->error('Reservasi tidak ditemukan', 404);

        $newStatus = $request->status_pembayaran;

        // Jika status terverifikasi dan belum ada No. Pemeriksaan, generate
        if ($newStatus === 'terverifikasi' && !$reservasi->no_pemeriksaan) {
            $reservasi->no_pemeriksaan = $this->generateNoPemeriksaan();
        }

        // Jika pembayaran gagal, status reservasi ikut batal
        if ($newStatus === 'gagal') {
            $reservasi->status_reservasi = 'batal';
        }

        $reservasi->status_pembayaran = $newStatus;
        $reservasi->save();

        return $this->success('Status pembayaran diperbarui', $reservasi);
    }

    /**
     * Update Status Reservasi (Menunggu, Dalam Proses, Selesai, Batal).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusReservasi(Request $request, $id)
    {
        $request->validate(['status_reservasi' => 'required']);

        $reservasi = Reservasi::find($id);
        if (!$reservasi) return $this->error('Reservasi tidak ditemukan', 404);

        $newStatus = $request->status_reservasi;

        // Auto update pembayaran jika status masuk 'dalam_proses'
        if ($newStatus === 'dalam_proses') {
            // Asumsi pembayaran lunas/terverifikasi jika sudah diproses dokter
            if ($reservasi->status_pembayaran === 'menunggu_pembayaran' || $reservasi->status_pembayaran === 'menunggu_verifikasi') {
                $reservasi->status_pembayaran = 'terverifikasi';
            }
            // Generate No. Pemeriksaan jika belum ada
            if (!$reservasi->no_pemeriksaan) {
                $reservasi->no_pemeriksaan = $this->generateNoPemeriksaan();
            }
        }

        // Auto update pembayaran jika status 'selesai'
        if ($newStatus === 'selesai') {
            $reservasi->status_pembayaran = 'terverifikasi';
        }

        // Auto update pembayaran jika status 'batal'
        if ($newStatus === 'batal') {
            $reservasi->status_pembayaran = 'gagal';
        }

        $reservasi->status_reservasi = $newStatus;
        $reservasi->save();

        return $this->success('Status reservasi diperbarui', $reservasi);
    }

    /**
     * Menyimpan data Reservasi yang dibuat secara manual oleh Admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                // Default value untuk reservasi manual
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

    /**
     * Menampilkan halaman form untuk mengedit Reservasi.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        // 1. Ambil data reservasi berdasarkan ID dengan relasi
        $reservasi = Reservasi::with(['rekamMedis', 'dokter', 'jadwal'])->find($id);

        // 2. Jika tidak ketemu
        if (!$reservasi) {
            return redirect()->route('reservasi.admin.index')->with('error', 'Data reservasi tidak ditemukan.');
        }

        // 3. Ambil data Master untuk dropdown di form edit
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get();
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();

        // 4. Tampilkan halaman UI edit
        return view('reservasi.edit', compact('reservasi', 'dokters', 'polis')); 
    }

    /**
     * Menyimpan perubahan data Reservasi (Update).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // 1. Cari data
        $reservasi = Reservasi::find($id);
        if (!$reservasi) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // 2. Update data
        $reservasi->tanggal_pesan = $request->tanggal_pesan;
        
        // Simpan dokter jika ada inputnya
        if ($request->has('dokter_id')) {
            $reservasi->dokter_id = $request->dokter_id;
        }
        
        // Simpan alasan jika ada (perlu dipastikan kolom 'alasan' ada di DB)
        if ($request->has('alasan')) {
             // $reservasi->alasan = $request->alasan; // Dibiarkan ter-komentar sesuai kode aslimu
        }

        // Simpan perubahan
        $reservasi->save();

        // 3. Kembali ke halaman utama dengan pesan sukses
        return redirect()->route('reservasi.admin.index')->with('success', 'Reservasi berhasil diperbarui!');
    }
}