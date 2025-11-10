<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function getRiwayat()
    {
        $riwayat = Reservasi::with(['pasien', 'dokter', 'jadwal', 'jadwal.poli'])
            ->orderBy('tanggal_pesan', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'no_pemeriksaan' => $item->no_pemeriksaan,
                    'dokter' => $item->dokter?->nama ?? '-',
                    'tanggal' => $item->tanggal_pesan,
                    'poli' => $item->jadwal?->poli?->nama_poli ?? '-',
                    'status_reservasi' => $item->status_reservasi,
                ];
            });

        return response()->json($riwayat);
    }
}
