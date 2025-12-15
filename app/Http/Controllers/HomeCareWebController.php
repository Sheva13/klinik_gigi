<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeCareWebController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('homecare_reservasi')
            ->join('rekam_medis', 'homecare_reservasi.pasien_id', '=', 'rekam_medis.rekam_medis')
            ->leftJoin('master_dokter', 'homecare_reservasi.dokter_id', '=', 'master_dokter.kode_dokter')
            ->select(
                'homecare_reservasi.*',
                'homecare_reservasi.no_pemeriksaan as no_reservasi',
                'homecare_reservasi.tanggal_pesan as tgl_reservasi',
                'rekam_medis.nama as nama_pasien',
                'master_dokter.nama as nama_dokter'
            );

        // 1. Filter Pencarian Nama Pasien
        if ($request->has('search') && $request->search != '') {
            $query->where('rekam_medis.nama', 'like', '%' . $request->search . '%');
        }

        // 2. Filter Rentang Tanggal
        if ($request->has('start_date') && $request->start_date != '' && $request->has('end_date') && $request->end_date != '') {
            $query->whereBetween('homecare_reservasi.tanggal_pesan', [$request->start_date, $request->end_date]);
        }

        // 3. Filter Status (BARU)
        if ($request->has('status') && $request->status != '') {
            $query->where('homecare_reservasi.status', $request->status);
        }

        // Ubah get() menjadi paginate(10) dan tambahkan withQueryString() agar filter tidak hilang saat pindah halaman
        $riwayat = $query->orderBy('homecare_reservasi.created_at', 'desc')
                         ->paginate(10)
                         ->withQueryString();

        return view('homecare.index', compact('riwayat'));
    }

    public function show($id)
    {
        $item = DB::table('homecare_reservasi')
            ->join('rekam_medis', 'homecare_reservasi.pasien_id', '=', 'rekam_medis.rekam_medis')
            ->leftJoin('master_dokter', 'homecare_reservasi.dokter_id', '=', 'master_dokter.kode_dokter')
            ->where('homecare_reservasi.id', $id)
            ->select(
                'homecare_reservasi.*',
                'homecare_reservasi.no_pemeriksaan as no_reservasi',
                'homecare_reservasi.tanggal_pesan as tgl_reservasi',
                'rekam_medis.nama as nama_pasien',
                'master_dokter.nama as nama_dokter'
            )
            ->first();

        if (!$item) {
            return redirect()->route('homecare.index')->with('error', 'Data tidak ditemukan');
        }

        return view('homecare.show', compact('item')); // Pastikan nama viewnya show.blade.php
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        DB::table('homecare_reservasi')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Status pemeriksaan berhasil diperbarui.');
    }
}