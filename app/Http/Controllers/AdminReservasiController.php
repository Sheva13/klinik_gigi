<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterPoli;
use App\Models\JadwalHarian; // <--- FIX: Memastikan Model yang benar terimport
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage; 

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
            'dokter',            
            'dokter.masterPoli', 
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
        $pasiens = RekamMedis::select('id', 'nama', 'rekam_medis')->get(); 
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get(); 
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();
        $jadwals = JadwalHarian::all(); // <--- FIX: Menggunakan JadwalHarian::all()

        // Memanggil view 'reservasi.reservasi_form'
        return view('reservasi.reservasi_form', compact('pasiens', 'dokters', 'polis', 'jadwals')); 
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createManual(Request $request)
    {
        // 1. VALIDASI BARU: Mencakup semua field dari reservasi_form.blade.php
        $request->validate([
            // Data Pasien (Baru atau Lama)
            'pasien_id_exist'   => 'nullable|exists:rekam_medis,id', 
            'nama_lengkap'      => 'required_without:pasien_id_exist|string|max:255', 
            'ttl'               => 'required_without:pasien_id_exist|string|max:255',
            'alamat'            => 'required_without:pasien_id_exist|string|max:255',
            'no_hp'             => 'required_without:pasien_id_exist|string|max:15',
            'jenis_pasien'      => 'required|string', 

            // Detail Janji Temu
            'poli'              => 'required|exists:master_poli,kode_poli', 
            'dokter'            => 'required|exists:master_dokter,kode_dokter',
            'tanggal_janji'     => 'required|date',
            'waktu_janji'       => 'required|date_format:H:i',
            'keluhan'           => 'nullable|string',
            
            // Informasi Pembayaran
            'metode_bayar'      => 'required|string|max:50',
            'status_bayar'      => 'required|string|max:50',
            'total_biaya'       => 'nullable|numeric',

        ], [
            'nama_lengkap.required_without' => 'Nama lengkap wajib diisi jika bukan pasien lama.',
            'ttl.required_without'          => 'Tempat, Tanggal Lahir wajib diisi jika bukan pasien lama.',
            'alamat.required_without'       => 'Alamat wajib diisi jika bukan pasien lama.',
            'no_hp.required_without'        => 'Nomor HP wajib diisi jika bukan pasien lama.',
        ]);

        DB::beginTransaction();
        try {
            // 2. LOGIC PENENTUAN PASIEN (BARU vs LAMA)
            $rekam_medis_id = $request->input('pasien_id_exist');
            $pasien_nama = $request->input('nama_lengkap') ?? 'Pasien Lama';

            if (!$rekam_medis_id) {
                // PASIEN BARU: Buat data di tabel RekamMedis
                $last_rm = RekamMedis::latest('id')->first();
                $new_rm_num = $last_rm ? (int) substr($last_rm->rekam_medis, 2) + 1 : 1;
                $rekam_medis_no = 'RM' . str_pad($new_rm_num, 3, '0', STR_PAD_LEFT);

                $pasien = RekamMedis::create([
                    'rekam_medis'  => $rekam_medis_no,
                    'nama'         => $request->input('nama_lengkap'),
                    'tgl_lahir'    => $request->input('ttl'),
                    'alamat'       => $request->input('alamat'),
                    'no_hp'        => $request->input('no_hp'),
                    'jenis_pasien' => $request->input('jenis_pasien'),
                ]);
                $rekam_medis_id = $pasien->id; 
                $pasien_nama = $pasien->nama;

            } else {
                 // PASIEN LAMA: Ambil nama pasien untuk pesan sukses
                 $pasien = RekamMedis::find($rekam_medis_id);
                 if($pasien) {
                    $pasien_nama = $pasien->nama;
                 }
            }


            // 3. LOGIC RESERVASI: Simpan data ke tabel Reservasi
            $tanggal_waktu_pesan = Carbon::parse($request->input('tanggal_janji') . ' ' . $request->input('waktu_janji'));
            
            // Cari jadwal_id yang cocok (Poli + Dokter + Hari)
            $day_of_week = $tanggal_waktu_pesan->dayOfWeekIso; // 1 (Senin) - 7 (Minggu)
            $jam_mulai = $tanggal_waktu_pesan->format('H:i:s'); 
            
            $jadwal = JadwalHarian::where('dokter_id', $request->input('dokter')) // <--- PERBAIKAN: Menggunakan JadwalHarian
                            ->where('hari', $day_of_week)
                            ->where('jam_mulai', '<=', $jam_mulai)
                            ->where('jam_selesai', '>', $jam_mulai)
                            ->first();

            $reservasi = Reservasi::create([
                // Data Pasien
                'pasien_id'         => $rekam_medis_id, 
                
                // Data Janji Temu
                'dokter_id'         => $request->input('dokter'), 
                'jadwal_id'         => $jadwal->id ?? null,
                'tanggal_pesan'     => $tanggal_waktu_pesan, 
                'keluhan'           => $request->input('keluhan'),

                // Data Pembayaran
                'status_pembayaran' => $request->input('status_bayar'), 
                'metode_pembayaran' => $request->input('metode_bayar'),
                'jumlah_total'      => $request->input('total_biaya', 0),
                
                // Status dan Nomor Otomatis
                'status_reservasi'  => $request->input('status_reservasi', 'menunggu'),
                'no_pemeriksaan'    => $this->generateNoPemeriksaan(),
            ]);

            DB::commit();
            
            // 4. RESPON: Mengubah return JSON lama ke redirect untuk web
            return redirect()->route('reservasi.admin.index')
                             ->with('success', 'Reservasi manual untuk ' . $pasien_nama . ' berhasil dibuat!');

        } catch (Exception $e) {
            DB::rollBack();
            // Mengubah return JSON error ke redirect error
            return redirect()->back()->with('error', 'Gagal membuat reservasi. Pastikan semua data benar. Error: ' . $e->getMessage())->withInput();
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

        // 3. UBAH [2]: Redirect ke halaman Pembayaran (Page 4) setelah sukses update jadwal
        return redirect()->route('admin.reservasi.pembayaran', ['id' => $reservasi->id])
                         ->with('success', 'Jadwal Reservasi berhasil diperbarui! Silakan tinjau pembayaran.');
    }

    // ============================================
    // === [3] TAMBAHAN METHOD BARU UNTUK PAGE 4 ===
    // ============================================

    /**
     * Menampilkan halaman Manajemen Pembayaran (Page 4).
     * Route: admin.reservasi.pembayaran
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showPayment($id)
    {
        // Ambil data reservasi dengan relasi yang dibutuhkan
        $reservasi = Reservasi::with(['rekamMedis', 'jadwal'])->find($id);

        if (!$reservasi) {
            return redirect()->route('reservasi.admin.index')->with('error', 'Reservasi tidak ditemukan.');
        }

        // Tampilkan view pembayaran (resources/views/reservasi/pembayaran.blade.php)
        return view('reservasi.pembayaran', compact('reservasi'));
    }

    /**
     * Memproses form update pembayaran dan menandai lunas.
     * Route: reservasi.admin.tandaiLunas
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function tandaiLunas(Request $request, $id)
    {
        // 1. Validasi
        $request->validate([
            'bukti_pembayaran' => 'required|file|mimes:jpeg,png,pdf|max:2048', 
        ]);

        $reservasi = Reservasi::find($id);
        if (!$reservasi) {
            return redirect()->back()->with('error', 'Reservasi tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            // 2. Handle File Upload
            if ($request->hasFile('bukti_pembayaran')) {
                // Simpan file ke direktori 'storage/app/public/bukti_bayar'
                $path = $request->file('bukti_pembayaran')->store('bukti_bayar', 'public');
                
                // Simpan nama path dan nama asli file ke database
                $reservasi->bukti_pembayaran_path = $path; 
                $reservasi->bukti_pembayaran_file_name = $request->file('bukti_pembayaran')->getClientOriginalName();
            }

            // 3. Update Status Pembayaran & Reservasi
            $reservasi->status_pembayaran = 'Lunas'; 
            
            // Tambahkan: Jika lunas, status reservasi bisa otomatis dianggap menunggu proses (jika belum)
            if ($reservasi->status_reservasi === 'menunggu_pembayaran' || $reservasi->status_reservasi === 'menunggu_verifikasi') {
                 $reservasi->status_reservasi = 'menunggu';
            }
            
            $reservasi->save();

            DB::commit();
            return redirect()->route('reservasi.admin.index')
                             ->with('success', 'Pembayaran berhasil ditandai LUNAS! Status reservasi diperbarui.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}