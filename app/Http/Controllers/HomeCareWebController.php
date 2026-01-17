<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeCareWebController extends Controller
{
    public function index(Request $request)
    {
        // PERBAIKAN: Menggunakan 'rekam_medis.hp' (sesuai database simklinik)
        $query = DB::table('homecare_reservasi')
            ->leftJoin('users', 'homecare_reservasi.pasien_id', '=', 'users.user_id')
            ->leftJoin('rekam_medis', 'users.rekam_medis_id', '=', 'rekam_medis.id')
            ->leftJoin('master_dokter', 'homecare_reservasi.dokter_id', '=', 'master_dokter.kode_dokter')
            ->select(
                'homecare_reservasi.*',
                'rekam_medis.nama as nama_pasien',
                'rekam_medis.hp as no_hp_pasien', // SEBELUMNYA 'no_hp' (Salah) -> SEKARANG 'hp' (Benar)
                'master_dokter.nama as nama_dokter'
            );

        // Filter Pencarian
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('rekam_medis.nama', 'like', '%' . $request->search . '%')
                  ->orWhere('homecare_reservasi.no_pemeriksaan', 'like', '%' . $request->search . '%');
            });
        }

        // Filter Status Grouping (Sesuai Reservasi: Menunggu, Diproses, Selesai, Batal)
        if ($request->has('status') && $request->status != '') {
            switch ($request->status) {
                case 'menunggu':
                    $query->whereIn('homecare_reservasi.status_reservasi', ['menunggu', 'menunggu_konfirmasi', 'menunggu_pembayaran', 'menunggu_dokter', 'terverifikasi']);
                    break;
                case 'diproses':
                    $query->whereIn('homecare_reservasi.status_reservasi', ['dokter_menuju_lokasi', 'sedang_diperiksa', 'dalam_pemeriksaan']);
                    break;
                case 'selesai':
                    $query->whereIn('homecare_reservasi.status_reservasi', ['selesai', 'lunas', 'menunggu_pelunasan']);
                    break;
                case 'batal':
                    $query->whereIn('homecare_reservasi.status_reservasi', ['dibatalkan', 'gagal', 'expired']);
                    break;
                default:
                    // Fallback jika ada status spesifik yang dikirim (misal lewat link detail)
                    $query->where('homecare_reservasi.status_reservasi', $request->status);
                    break;
            }
        }

        // Filter Tanggal
        if ($request->has('start_date') && $request->start_date != '') {
            if ($request->has('end_date') && $request->end_date != '') {
                // Range
                $query->whereBetween('homecare_reservasi.tanggal_pesan', [$request->start_date, $request->end_date]);
            } else {
                // Single Date
                $query->whereDate('homecare_reservasi.tanggal_pesan', $request->start_date);
            }
        }

        $riwayat = $query->orderBy('homecare_reservasi.created_at', 'desc')
                         ->paginate(10)
                         ->withQueryString();

        // --- STATISTIK ---
        // Refactored to single array to match Blade expectation
        $stats = [
            'total'     => DB::table('homecare_reservasi')->count(),
            'menunggu'  => DB::table('homecare_reservasi')
                            ->whereIn('status_reservasi', ['menunggu', 'menunggu_konfirmasi', 'menunggu_pembayaran', 'menunggu_dokter', 'terverifikasi'])
                            ->count(),
            'diproses'  => DB::table('homecare_reservasi')
                            ->whereIn('status_reservasi', ['dokter_menuju_lokasi', 'sedang_diperiksa', 'dalam_pemeriksaan']) 
                            ->count(),
            'selesai'   => DB::table('homecare_reservasi')
                            ->whereIn('status_reservasi', ['menunggu_pelunasan', 'lunas', 'selesai'])
                            ->count(),
            'batal'     => DB::table('homecare_reservasi')
                            ->whereIn('status_reservasi', ['dibatalkan', 'expire', 'gagal', 'batal'])
                            ->count(),
        ];

        return view('homecare.index', compact('riwayat', 'stats'));
    }

    public function show($id)
    {
        $item = DB::table('homecare_reservasi')
            ->leftJoin('users', 'homecare_reservasi.pasien_id', '=', 'users.user_id')
            ->leftJoin('rekam_medis', 'users.rekam_medis_id', '=', 'rekam_medis.id')
            ->leftJoin('master_dokter', 'homecare_reservasi.dokter_id', '=', 'master_dokter.kode_dokter')
            ->where('homecare_reservasi.id', $id)
            ->select(
                'homecare_reservasi.*',
                'rekam_medis.nama as nama_pasien',
                'users.nama_pengguna as nama_user',
                'rekam_medis.hp as no_hp_pasien', // PERBAIKAN DI SINI JUGA
                'rekam_medis.rekam_medis as no_rm',
                'users.nama_pengguna as nama_user',
                'rekam_medis.tanggal_lahir',
                'rekam_medis.jenis_kelamin',
                'rekam_medis.alamat as alamat_ktp',
                'master_dokter.nama as nama_dokter'
            )
            ->first();

        if (!$item) {
            return redirect()->route('homecare.index')->with('error', 'Data reservasi tidak ditemukan');
        }

        return view('homecare.show', compact('item'));
    }

    // --- ANTRIAN MANAGEMENT ---
    public function antrianIndex(Request $request)
    {
        $tanggalPilih = $request->input('tanggal', \Carbon\Carbon::today()->format('Y-m-d'));

        // Query Reservasi HomeCare
        $query = DB::table('homecare_reservasi')
            ->leftJoin('users', 'homecare_reservasi.pasien_id', '=', 'users.user_id')
            ->leftJoin('rekam_medis', 'users.rekam_medis_id', '=', 'rekam_medis.id')
            ->leftJoin('master_dokter', 'homecare_reservasi.dokter_id', '=', 'master_dokter.kode_dokter')
            ->select(
                'homecare_reservasi.*',
                'rekam_medis.nama as nama_pasien',
                'rekam_medis.rekam_medis as no_rm',
                'master_dokter.nama as nama_dokter'
            )
            ->whereDate('homecare_reservasi.tanggal_pesan', $tanggalPilih)
            ->whereIn('homecare_reservasi.status_reservasi', ['menunggu_konfirmasi', 'dokter_menuju_lokasi', 'sedang_diperiksa', 'menunggu_pelunasan', 'lunas']) // Filter status aktif
            ->orderBy('homecare_reservasi.jam_mulai', 'asc');

        // Search Filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('homecare_reservasi.no_pemeriksaan', 'LIKE', "%$search%")
                  ->orWhere('rekam_medis.nama', 'LIKE', "%$search%");
            });
        }

        $antrian = $query->get();

        // Manual Stats Counters for Queue Page
        $stats = [
            'menunggu' => $antrian->whereIn('status_reservasi', ['menunggu_konfirmasi', 'dokter_menuju_lokasi'])->count(),
            'diproses' => $antrian->where('status_reservasi', 'sedang_diperiksa')->count(),
            'selesai'  => $antrian->whereIn('status_reservasi', ['menunggu_pelunasan', 'lunas'])->count(),
            'batal'    => DB::table('homecare_reservasi')
                            ->whereDate('tanggal_pesan', $tanggalPilih)
                            ->whereIn('status_reservasi', ['dibatalkan', 'gagal'])
                            ->count(),
        ];

        return view('homecare.pasienhomecare', compact('antrian', 'stats', 'tanggalPilih'));
    }

    public function updateStatus(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'status' => 'required|string',
        ]);

        // LOGIC KHUSUS: Wajib input biaya saat status 'menunggu_pelunasan'
        if ($request->status === 'menunggu_pelunasan') {
            $request->validate([
                'total_biaya_tindakan' => 'required|numeric|min:0'
            ], [
                'total_biaya_tindakan.required' => 'Wajib mengisi Total Biaya saat tindakan selesai!'
            ]);
        }

        // 2. Siapkan Data Update
        $dataUpdate = [
            'status_reservasi' => $request->status,
            'updated_at' => now()
        ];

        // Update kolom 'status' (bacaan manusia) agar sinkron
        $readableStatus = [
            'menunggu_konfirmasi'   => 'Menunggu Konfirmasi Admin',
            'dokter_menuju_lokasi'  => 'Dokter Sedang Menuju Lokasi',
            'sedang_diperiksa'      => 'Sedang Dalam Pemeriksaan',
            'menunggu_pelunasan'    => 'Pemeriksaan Selesai (Menunggu Pembayaran)',
            'lunas'                 => 'Layanan Selesai & Lunas',
            'dibatalkan'            => 'Dibatalkan'
        ];
        
        if (isset($readableStatus[$request->status])) {
            $dataUpdate['status'] = $readableStatus[$request->status];
        } else {
            $dataUpdate['status'] = ucwords(str_replace('_', ' ', $request->status));
        }

        // Fix: Default total_biaya_tindakan to 0 to prevent null error on Batal/Lunas
        $dataUpdate['total_biaya_tindakan'] = $request->total_biaya_tindakan ?? 0;

        // Simpan Biaya Tindakan jika ada (Override if provided)
        if ($request->has('total_biaya_tindakan')) {
            $dataUpdate['total_biaya_tindakan'] = $request->total_biaya_tindakan;
        }

        // Logic Sinkronisasi status_booking (Payment Status)
        if ($request->status === 'lunas' || $request->status === 'selesai' || $dataUpdate['status'] === 'Selesai') {
            $dataUpdate['status_booking'] = 'lunas';
        } elseif ($request->status === 'dibatalkan') {
            $dataUpdate['status_booking'] = 'gagal';
        }

        // 3. Eksekusi Update
        DB::table('homecare_reservasi')
            ->where('id', $id)
            ->update($dataUpdate);

        // --- 4. LOGIC POIN (Perbaikan Integrasi) ---
        // Jika status diubah menjadi 'Selesai' (Lunas), berikan poin ke user
        if (
            ($dataUpdate['status'] === 'Selesai' || $dataUpdate['status'] === 'Lunas') ||
            ($request->status === 'lunas' || $request->status === 'menunggu_pelunasan' /* 'menunggu_pelunasan' belum lunas */)
        ) {
             // Cek jika status akhir benar-benar lunas/selesai
             // Note: Mapping $readableStatus di atas mengubah 'lunas' -> 'Selesai'
             if ($dataUpdate['status'] === 'Selesai') {
                $reservasi = DB::table('homecare_reservasi')->where('id', $id)->first();
                
                // Check if poin already added for this transaction (use type='earn' for reliable check)
                $exists = \App\Models\PointHistory::where('reference_id', $reservasi->no_pemeriksaan)
                            ->where('type', 'earn')
                            ->exists();

                if (!$exists && $reservasi->pasien_id) {
                    // FIXED: Only calculate from total_biaya_tindakan (pelunasan), not the booking amount
                    // Booking poin is already handled separately when booking payment is confirmed
                    $totalBayar = $reservasi->total_biaya_tindakan ?? 0;
                    $poinDidapat = floor($totalBayar / 10000);

                    if ($poinDidapat > 0) {
                        // 1. Tambah Poin User
                        DB::table('users')->where('user_id', $reservasi->pasien_id)->increment('poin', $poinDidapat);
                        
                        // 2. Catat History
                        \App\Models\PointHistory::create([
                            'user_id' => $reservasi->pasien_id,
                            'amount' => $poinDidapat,
                            'type' => 'earn',
                            'description' => 'Poin dari HomeCare (Update Admin)',
                            'reference_id' => $reservasi->no_pemeriksaan
                        ]);
                    }
                }
             }
        }

        // 5. Log Tracking (Opsional)
        try {
            DB::table('home_care_tracking')->insert([
                'id_periksa' => $id,
                'status_tracking' => $request->status,
                'keterangan' => 'Update status via Web Admin',
                'waktu' => now(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Abaikan error tracking jika tabel belum siap/beda struktur
        }

        return redirect()->back()->with('success', 'Status reservasi berhasil diperbarui.');
    }
}