<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservasi;

class ReservasiController extends Controller
{
    public function getRiwayat()
    {
        $riwayat = Reservasi::orderBy('no_pemeriksaan', 'desc')->get();
        return response()->json($riwayat);
    }
}
