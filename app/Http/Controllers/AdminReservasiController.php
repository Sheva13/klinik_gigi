<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Reservasi;
use Carbon\Carbon;
use Exception;

class AdminReservasiController extends Controller
{
    /* ============================================
        🔹 Helper JSON Response
    ============================================ */
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


    /* ============================================
        🔹 LIST RESERVASI (Optimal & Sesuai UI)
        Perbaikan: Menghilangkan select kolom yang tidak ada (poli_id)
        dan mengganti rekam_medis_id menjadi pasien_id.
    ============================================ */
    public function index(Request $request)
    {
        $query = Reservasi::with([
            // Mengambil No RM dan Nama Pasien dari relasi rekamMedis
            'rekamMedis:rekam_medis,nama', 
            
            // Menggunakan relasi bersarang untuk mendapatkan data Poli (Dokter -> Poli)
            'dokter.masterPoli:kode_poli,nama_poli', 
            'dokter:kode_dokter,nama,kode_poli', 

            'jadwal:id,hari,jam_mulai,jam_selesai'
        ])->select([
            'id',
            'pasien_id', // ✅ Perbaikan: Menggunakan FK yang benar
            'dokter_id',
            // 'poli_id' dihilangkan
            'jadwal_id',
            'tanggal_pesan',
            'status_pembayaran',
            'status_reservasi',
            'no_pemeriksaan',
            'created_at'
        ]);

        // 🔍 Filter No RM
        if ($request->no_rm) {
            $query->whereHas('rekamMedis', function ($q) use ($request) {
                $q->where('rekam_medis', 'LIKE', "%{$request->no_rm}%");
            });
        }

        // 🔍 Filter Poli (menggunakan relasi bersarang)
        if ($request->poli_id && $request->poli_id !== "semua") {
            $query->whereHas('dokter.masterPoli', function ($q) use ($request) {
                $q->where('kode_poli', $request->poli_id); 
            });
        }

        // 🔍 Filter Dokter
        if ($request->dokter_id && $request->dokter_id !== "semua") {
            $query->where('dokter_id', $request->dokter_id);
        }

        // 🔍 Filter Status Pembayaran
        if ($request->status_pembayaran && $request->status_pembayaran !== "semua") {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        // 🔍 Filter Status Reservasi
        if ($request->status_reservasi && $request->status_reservasi !== "semua") {
            $query->where('status_reservasi', $request->status_reservasi);
        }

        $data = $query
            ->orderBy('tanggal_pesan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10); // 💡 Menggunakan paginate agar $data->links() berfungsi di Blade

        // ✅ PERBAIKAN: Mengembalikan View dan mengirim data ($data)
        return view('reservasi.index', compact('data'));
    }


    /* ============================================
        🔹 DETAIL RESERVASI
        Penyesuaian: Mengganti relasi 'poli' langsung menjadi relasi bersarang
    ============================================ */
    public function show($id)
    {
        $reservasi = Reservasi::with([
            'rekamMedis',
            'dokter',
            'dokter.masterPoli', // ✅ Menggunakan relasi bersarang yang benar
            'jadwal'
        ])->find($id);

        // Mengubah nama variabel dari $data menjadi $reservasi agar cocok dengan show.blade.php
        
        if (!$reservasi) {
            return $this->error('Reservasi tidak ditemukan', 404);
        }

        // ✅ PERBAIKAN: Mengembalikan View dan mengirim data ($reservasi)
        return view('admin.reservasi.show', compact('reservasi'));
    }


    /* ============================================
        🔹 Generate Nomor Pemeriksaan
        Format: RSV-YYYYMMDDXXX
    ============================================ */
    private function generateNoPemeriksaan()
    {
        $tanggal = Carbon::now()->format('Ymd');

        do {
            $random = random_int(100, 999);
            $no = "RSV-{$tanggal}{$random}";
        } while (Reservasi::where('no_pemeriksaan', $no)->exists());

        return $no;
    }


    /* ============================================
        🔹 UPDATE STATUS PEMBAYARAN (Admin)
        Enum: waiting, verified, cancelled
    ============================================ */
    public function updatePembayaran(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:waiting,verified,cancelled'
        ]);

        $reservasi = Reservasi::find($id);
        if (!$reservasi) return $this->error('Reservasi tidak ditemukan', 404);

        $newStatus = $request->status_pembayaran;

        // Jika diverifikasi → generate nomor pemeriksaan
        if ($newStatus === 'verified' && !$reservasi->no_pemeriksaan) {
            $reservasi->no_pemeriksaan = $this->generateNoPemeriksaan();
        }

        // Sinkronisasi otomatis ke status_reservasi
        if ($newStatus === 'cancelled') {
            $reservasi->status_reservasi = 'cancelled';
        }

        $reservasi->status_pembayaran = $newStatus;
        $reservasi->save();

        return $this->success('Status pembayaran diperbarui', $reservasi);
    }


    /* ============================================
        🔹 UPDATE STATUS RESERVASI
        Enum: waiting, process, completed, cancelled
    ============================================ */
    public function updateStatusReservasi(Request $request, $id)
    {
        $request->validate([
            'status_reservasi' => 'required|in:waiting,process,completed,cancelled'
        ]);

        $reservasi = Reservasi::find($id);
        if (!$reservasi) return $this->error('Reservasi tidak ditemukan', 404);

        $newStatus = $request->status_reservasi;

        // Jika proses → otomatis dianggap verified
        if ($newStatus === 'process') {
            if ($reservasi->status_pembayaran === 'waiting') {
                $reservasi->status_pembayaran = 'verified';
            }

            if (!$reservasi->no_pemeriksaan) {
                $reservasi->no_pemeriksaan = $this->generateNoPemeriksaan();
            }
        }

        // Jika completed → pembayaran harus verified
        if ($newStatus === 'completed') {
            $reservasi->status_pembayaran = 'verified';
        }

        // Jika cancelled → pembayaran dibatalkan
        if ($newStatus === 'cancelled') {
            $reservasi->status_pembayaran = 'cancelled';
        }

        $reservasi->status_reservasi = $newStatus;
        $reservasi->save();

        return $this->success('Status reservasi diperbarui', $reservasi);
    }


    /* ============================================
        🔹 ADMIN MENAMBAH RESERVASI MANUAL
        Penyesuaian: Mengganti kolom 'rekam_medis_id' dan menghilangkan 'poli_id'
    ============================================ */
    public function createManual(Request $request)
    {
        $request->validate([
            // Menggunakan kolom dan tabel yang benar untuk validasi
            'pasien_id'      => 'required|exists:rekam_medis,rekam_medis', 
            'dokter_id'      => 'required|exists:master_dokter,kode_dokter', 
            'poli_id'        => 'required|exists:master_poli,kode_poli', 
            'jadwal_id'      => 'required|exists:master_jadwal,id',
            'tanggal_pesan'  => 'required|date'
        ]);

        DB::beginTransaction();
        try {
            $reservasi = Reservasi::create([
                // Menggunakan kolom yang benar
                'pasien_id'         => $request->pasien_id,
                
                'dokter_id'         => $request->dokter_id,
                
                // 'poli_id' dihilangkan dari INSERT karena tidak ada di tabel reservasi
                
                'jadwal_id'         => $request->jadwal_id,
                'tanggal_pesan'     => $request->tanggal_pesan,

                'status_pembayaran' => 'verified',
                'status_reservasi'  => 'waiting',
                'no_pemeriksaan'    => $this->generateNoPemeriksaan()
            ]);

            DB::commit();
            return $this->success('Reservasi manual berhasil dibuat', $reservasi);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Gagal membuat reservasi manual: ' . $e->getMessage(), 500);
        }
    }
}