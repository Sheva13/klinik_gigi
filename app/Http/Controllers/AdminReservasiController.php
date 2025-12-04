<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterDokter;
use App\Models\MasterPoli;
use App\Models\JadwalHarian; 
use App\Models\MasterJadwal;
use App\Models\DataPasien;       // ✅ Model untuk Antrian Dokter
use App\Models\TransaksiBayar;   // ✅ Model untuk Kasir
use Carbon\Carbon;
use Exception;

class AdminReservasiController extends Controller
{
    // ============================================================
    // HELPER RESPONSE (API)
    // ============================================================
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

    // ============================================================
    // 1. INDEX (DAFTAR RESERVASI & STATISTIK)
    // ============================================================
    public function index(Request $request)
    {
        // Query Dasar dengan Relasi
        $query = Reservasi::with([
            'rekamMedis:rekam_medis,nama', 
            'dokter',             
            'dokter.masterPoli', 
            'jadwal:id,hari,jam_mulai,jam_selesai'
        ]); 

        // --- FILTERING LOGIC ---
        if ($request->no_rm) {
            $query->whereHas('rekamMedis', function ($q) use ($request) {
                $q->where('rekam_medis', 'LIKE', "%{$request->no_rm}%")
                  ->orWhere('nama', 'LIKE', "%{$request->no_rm}%");
            });
        }
        if ($request->poli_id && $request->poli_id !== "semua") {
            $query->whereHas('dokter.masterPoli', function ($q) use ($request) {
                $q->where('kode_poli', $request->poli_id); 
            });
        }
        if ($request->dokter_id && $request->dokter_id !== "semua") {
            $query->where('dokter_id', $request->dokter_id);
        }
        if ($request->status_pembayaran && $request->status_pembayaran !== "semua") {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }
        if ($request->status_reservasi && $request->status_reservasi !== "semua") {
            $query->where('status_reservasi', $request->status_reservasi);
        }

        // --- STATISTIK CARD ---
        $statsQuery = clone $query; 
        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'menunggu' => (clone $statsQuery)->whereIn('status_reservasi', ['menunggu'])->count(),
            
            // 🔥 FIX: Menambahkan Counter 'Diproses' sesuai request Alia
            'diproses' => (clone $statsQuery)->whereIn('status_reservasi', ['dalam_proses'])->count(),
            
            'selesai'  => (clone $statsQuery)->whereIn('status_reservasi', ['selesai'])->count(),
            'batal'    => (clone $statsQuery)->whereIn('status_reservasi', ['batal'])->count(),
        ];

        // --- DATA PAGINATION ---
        $data = $query->orderBy('tanggal_pesan', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);

        $dokters = MasterDokter::select('kode_dokter', 'nama')->get();
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();

        return view('reservasi.index', compact('data', 'stats', 'dokters', 'polis'));
    }

    // ============================================================
    // 2. CREATE (FORM MANUAL) & AJAX SEARCH
    // ============================================================
    public function create()
    {
        $pasiens = RekamMedis::select('id', 'nama', 'rekam_medis')->get(); 
        $dokters = MasterDokter::select('kode_dokter', 'nama')->get(); 
        $polis   = MasterPoli::select('kode_poli', 'nama_poli')->get();
        $jadwals = JadwalHarian::all(); 
        return view('reservasi.reservasi_form', compact('pasiens', 'dokters', 'polis', 'jadwals')); 
    }

    // 🔥 AJAX: Cari Pasien Lama (Dipakai JS Frontend)
    public function cariPasien(Request $request)
    {
        if ($request->has('q')) {
            $cari = $request->q;
            $data = RekamMedis::select('id', 'rekam_medis', 'nama', 'tgl_lahir', 'alamat', 'no_hp', 'jenis_pasien')
                    ->where('rekam_medis', 'LIKE', "%$cari%")
                    ->orWhere('nama', 'LIKE', "%$cari%")
                    ->limit(5)->get();
            
            // Mapping Alias agar sesuai dengan JS di reservasi_form.blade.php
            $hasil = $data->map(function($item) {
                return [
                    'id_database'     => $item->id,
                    'nomor_rm'        => $item->rekam_medis,
                    'nama_lengkap'    => $item->nama,
                    'label_pencarian' => $item->rekam_medis . ' - ' . $item->nama,
                    'tanggal_lahir'   => $item->tgl_lahir,
                    'alamat_rumah'    => $item->alamat,
                    'nomor_telepon'   => $item->no_hp,
                    'tipe_pasien'     => $item->jenis_pasien
                ];
            });

            return response()->json($hasil);
        }
        return response()->json([]);
    }

    public function createManual(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required_without:pasien_id_exist|string|max:255', 
            'poli'         => 'required|exists:master_poli,kode_poli', 
            'dokter'       => 'required|exists:master_dokter,kode_dokter',
            'tanggal_janji'=> 'required|date',
            'waktu_janji'  => 'required',
        ]);

        DB::beginTransaction();
        try {
            // A. Logic Pasien (Baru atau Lama)
            $rekam_medis_id = $request->input('pasien_id_exist');
            $pasien_nama = $request->input('nama_lengkap') ?? 'Pasien Lama';
            $rmString = ''; 

            if (!$rekam_medis_id) {
                // Buat Pasien Baru
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
                $rmString = $pasien->rekam_medis;
            } else {
                 // Pakai Pasien Lama
                 $pasien = RekamMedis::find($rekam_medis_id);
                 if($pasien) { 
                    $pasien_nama = $pasien->nama; 
                    $rmString = $pasien->rekam_medis; 
                 }
            }

            // B. Logic Jadwal
            $tanggal_waktu_pesan = Carbon::parse($request->input('tanggal_janji') . ' ' . $request->input('waktu_janji'));
            $day_of_week = $tanggal_waktu_pesan->dayOfWeekIso; 
            
            // Cek Jadwal di Master
            $master = MasterJadwal::where('kode_dokter', $request->input('dokter'))
                                  ->where('hari', $day_of_week)
                                  ->first();
            $jadwal_id = $master ? $master->id : null;

            if (!$jadwal_id) throw new Exception("Jadwal Dokter tidak ditemukan di hari tersebut.");

            // C. Simpan Reservasi
            $reservasi = Reservasi::create([
                'pasien_id'         => $rmString, 
                'dokter_id'         => $request->input('dokter'), 
                'jadwal_id'         => $jadwal_id,
                'tanggal_pesan'     => $tanggal_waktu_pesan, 
                'jam_mulai'         => $request->input('waktu_janji'),
                'jam_selesai'       => Carbon::parse($request->input('waktu_janji'))->addMinutes(30)->format('H:i'),
                'keluhan'           => $request->input('keluhan'),
                'status_pembayaran' => $request->input('status_bayar'), 
                'metode_pembayaran' => $request->input('metode_bayar'),
                'pembayaran_total'  => $request->input('total_biaya', 0),
                'status_reservasi'  => 'menunggu',
                'no_pemeriksaan'    => $this->generateNoPemeriksaan(),
                'no_antrian'        => null, 
                'jenis_pasien'      => $request->input('jenis_pasien'), 
            ]);

            // 🔥 TRIGGER: Jika Admin set "Lunas" di awal, langsung buat antrian
            if (in_array($request->input('status_bayar'), ['lunas', 'terverifikasi'])) {
                $this->processQueueLogic($reservasi, $rmString, $jadwal_id);
            }

            DB::commit();
            return $this->success('Reservasi berhasil dibuat untuk ' . $pasien_nama);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Gagal: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 3. SHOW (DETAIL)
    // ============================================================
    public function show($id)
    {
        $reservasi = Reservasi::with(['rekamMedis', 'dokter', 'jadwal.poli'])->findOrFail($id);
        $dokters = MasterDokter::all(); 
        return view('reservasi.show', compact('reservasi', 'dokters'));
    }

    // ============================================================
    // 4. EDIT & UPDATE
    // ============================================================
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
        $reservasi = Reservasi::find($id);
        if (!$reservasi) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        // 1. Update Tanggal & Dokter
        $reservasi->tanggal_pesan = $request->tanggal_pesan;
        if ($request->has('dokter_id')) {
            $reservasi->dokter_id = $request->dokter_id;
        }
        
        // 2. Update Jam Praktek & Alasan (Sesuai form edit)
        if ($request->filled('jam_praktek')) {
            $reservasi->jam_mulai = $request->jam_praktek;
            $reservasi->jam_selesai = Carbon::parse($request->jam_praktek)->addMinutes(30)->format('H:i');
        }
        if ($request->filled('alasan')) {
            $reservasi->keluhan = $reservasi->keluhan . ' [Note: ' . $request->alasan . ']';
        }

        // 🔥 FIX: Update Status Pembayaran dari Edit Page
        if ($request->filled('status_pembayaran')) {
            $reservasi->status_pembayaran = $request->status_pembayaran;
            if ($request->status_pembayaran == 'terverifikasi' || $request->status_pembayaran == 'Lunas') {
                $reservasi->status_reservasi = 'menunggu';
                $rmString = $reservasi->rekamMedis ? $reservasi->rekamMedis->rekam_medis : $reservasi->pasien_id;
                $this->processQueueLogic($reservasi, $rmString, $reservasi->jadwal_id);
            }
        }

        $reservasi->save();
        return redirect()->route('reservasi.admin.show', $reservasi->id)->with('success', 'Data berhasil diperbarui!');
    }

    // ============================================================
    // 5. PEMBAYARAN & LUNAS
    // ============================================================
    public function showPayment($id)
    {
        $reservasi = Reservasi::with(['rekamMedis', 'jadwal'])->findOrFail($id);
        return view('reservasi.pembayaran', compact('reservasi'));
    }

    public function tandaiLunas(Request $request, $id)
    {
        $reservasi = Reservasi::with('rekamMedis')->findOrFail($id);
        DB::beginTransaction();
        try {
            // Upload File jika ada
            if ($request->hasFile('bukti_pembayaran')) {
                $path = $request->file('bukti_pembayaran')->store('bukti_bayar', 'public');
                $reservasi->bukti_pembayaran_path = $path; 
                $reservasi->bukti_pembayaran_file_name = $request->file('bukti_pembayaran')->getClientOriginalName();
            }

            $reservasi->status_pembayaran = 'terverifikasi'; 
            $reservasi->status_reservasi = 'menunggu'; 
            
            // 🔥 Trigger Logic Antrian
            $rmString = $reservasi->rekamMedis ? $reservasi->rekamMedis->rekam_medis : $reservasi->pasien_id;
            $this->processQueueLogic($reservasi, $rmString, $reservasi->jadwal_id);

            DB::commit();
            return redirect()->route('reservasi.admin.index')->with('success', 'LUNAS! Pasien Masuk Antrian.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ✅ FUNGSI UPDATE PEMBAYARAN MANUAL (DROPDOWN)
    public function updatePembayaran(Request $request, $id)
    {
        $reservasi = Reservasi::with('rekamMedis')->findOrFail($id);
        DB::beginTransaction();
        try {
            // Ambil status dari dropdown
            $statusBaru = $request->input('status_pembayaran', 'terverifikasi');
            $reservasi->status_pembayaran = $statusBaru;

            // Jika Lunas, generate antrian
            if ($statusBaru == 'terverifikasi' || $statusBaru == 'Lunas') {
                $reservasi->status_reservasi = 'menunggu';
                $rmString = $reservasi->rekamMedis ? $reservasi->rekamMedis->rekam_medis : $reservasi->pasien_id;
                $this->processQueueLogic($reservasi, $rmString, $reservasi->jadwal_id);
            }

            $reservasi->save();
            DB::commit();
            return back()->with('success', 'Status Pembayaran Diperbarui');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function updateStatusReservasi(Request $request, $id)
    {
        $reservasi = Reservasi::findOrFail($id);
        $request->validate(['status_reservasi' => 'required|in:menunggu,dalam_proses,selesai,batal']);
        $reservasi->status_reservasi = $request->status_reservasi;
        $reservasi->save();
        return back()->with('success', 'Status Kunjungan Diperbarui');
    }

    // ============================================================
    // PRIVATE HELPER (LOGIC INTI)
    // ============================================================
    private function generateNoPemeriksaan() {
        $tanggal = Carbon::now()->format('Ymd');
        do { $random = random_int(100, 999); $no = "RSV-{$tanggal}{$random}"; } 
        while (Reservasi::where('no_pemeriksaan', $no)->exists());
        return $no;
    }

    // 🔥🔥🔥 FIX PENTING ADA DI SINI 🔥🔥🔥
    private function processQueueLogic($reservasi, $rmString, $jadwalId) {
        // 1. Generate Antrian
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

        // 2. Insert DataPasien
        $cekAntrian = DataPasien::where('rekam_medis', $rmString)->where('id_jadwal', $jadwalId)->whereDate('created_at', Carbon::today())->first();
        $idPeriksa = null;
        if (!$cekAntrian) {
            $dp = DataPasien::create([
                'id_jadwal' => $jadwalId, 'rekam_medis' => $rmString, 'no_antri' => $urutanBaru,
                'status' => 1, 'pasien_baru' => 0, 'rujukan' => 0, 'biaya_admin' => 0, 'keluhan' => $reservasi->keluhan,
            ]);
            $idPeriksa = $dp->id;
        } else { $idPeriksa = $cekAntrian->id; }

        // 3. Insert TransaksiBayar
        $cekTrx = TransaksiBayar::where('id_periksa', $idPeriksa)->first();
        if (!$cekTrx && $idPeriksa) {
            TransaksiBayar::create([
                'id_periksa' => $idPeriksa, 
                
                // 🔥 FIX UTAMA: Tambahkan 'ambil_obat' => 0 agar tidak error database
                'ambil_obat' => 0, 

                'total_tindakan' => 0, 'total_obat' => 0, 'total_penunjang' => 0,
                'total_tambahan' => 0, 'total_bayar' => 0, 'waktu' => Carbon::now(), 'diskon' => 0, 'biaya_admin' => 0, 'pasien_baru' => 0,
            ]);
        }
    }
}