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
            ->join('rekam_medis', 'homecare_reservasi.pasien_id', '=', 'rekam_medis.id')
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

        // Filter Status
        if ($request->has('status') && $request->status != '') {
            $query->where('homecare_reservasi.status_reservasi', $request->status);
        }

        // Filter Tanggal
        if ($request->has('start_date') && $request->start_date != '' && $request->has('end_date') && $request->end_date != '') {
            $query->whereBetween('homecare_reservasi.tanggal_pesan', [$request->start_date, $request->end_date]);
        }

        $riwayat = $query->orderBy('homecare_reservasi.created_at', 'desc')
                         ->paginate(10)
                         ->withQueryString();

        return view('homecare.index', compact('riwayat'));
    }

    public function show($id)
    {
        $item = DB::table('homecare_reservasi')
            ->join('rekam_medis', 'homecare_reservasi.pasien_id', '=', 'rekam_medis.id')
            ->leftJoin('master_dokter', 'homecare_reservasi.dokter_id', '=', 'master_dokter.kode_dokter')
            ->where('homecare_reservasi.id', $id)
            ->select(
                'homecare_reservasi.*',
                'rekam_medis.nama as nama_pasien',
                'rekam_medis.hp as no_hp_pasien', // PERBAIKAN DI SINI JUGA
                'rekam_medis.alamat as alamat_ktp',
                'master_dokter.nama as nama_dokter'
            )
            ->first();

        if (!$item) {
            return redirect()->route('homecare.index')->with('error', 'Data reservasi tidak ditemukan');
        }

        return view('homecare.show', compact('item'));
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
            'menunggu_konfirmasi'   => 'Menunggu Konfirmasi',
            'dokter_menuju_lokasi'  => 'Dokter OTW',
            'sedang_diperiksa'      => 'Sedang Diperiksa',
            'menunggu_pelunasan'    => 'Menunggu Pembayaran',
            'lunas'                 => 'Selesai',
            'dibatalkan'            => 'Dibatalkan'
        ];
        
        if (isset($readableStatus[$request->status])) {
            $dataUpdate['status'] = $readableStatus[$request->status];
        } else {
            $dataUpdate['status'] = ucwords(str_replace('_', ' ', $request->status));
        }

        // Simpan Biaya Tindakan jika ada
        if ($request->has('total_biaya_tindakan')) {
            $dataUpdate['total_biaya_tindakan'] = $request->total_biaya_tindakan;
        }

        // 3. Eksekusi Update
        DB::table('homecare_reservasi')
            ->where('id', $id)
            ->update($dataUpdate);

        // 4. Log Tracking (Opsional)
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