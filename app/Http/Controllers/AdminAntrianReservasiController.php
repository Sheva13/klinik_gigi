<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservasi;
use Carbon\Carbon;

class AdminAntrianReservasiController extends Controller
{
    public function antrianIndex(Request $request)
    {
        $tanggalPilih = $request->input('tanggal', Carbon::today()->format('Y-m-d'));

        $query = Reservasi::with(['rekamMedis', 'dokter'])
            ->whereDate('tanggal_pesan', $tanggalPilih)
            ->whereNotNull('no_antrian')
            ->whereIn('status_pembayaran', ['lunas', 'terverifikasi'])
            ->orderByRaw("FIELD(status_reservasi, 'dalam_proses', 'menunggu', 'selesai', 'batal')")
            ->orderByRaw("CAST(SUBSTRING_INDEX(no_antrian, '-', -1) AS UNSIGNED) ASC");

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_antrian', 'LIKE', "%$search%")
                  ->orWhereHas('rekamMedis', fn($q2) => $q2->where('nama', 'LIKE', "%$search%"));
            });
        }

        $antrian = $query->get();

        $stats = [
            'menunggu' => $antrian->where('status_reservasi', 'menunggu')->count(),
            'diproses' => $antrian->where('status_reservasi', 'dalam_proses')->count(),
            'selesai'  => $antrian->where('status_reservasi', 'selesai')->count(),
            'batal'    => $antrian->where('status_reservasi', 'batal')->count(),
        ];

        return view('reservasi.patient-queue', compact('antrian', 'stats', 'tanggalPilih'));
    }
}