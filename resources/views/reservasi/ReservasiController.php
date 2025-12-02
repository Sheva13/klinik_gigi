<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservasi; // Pastikan Model Reservasi di-import

class ReservasiController extends Controller
// [ ... fungsi index() dihilangkan untuk brevity ... ]
{

    /**
     * Menampilkan detail reservasi spesifik (Detail - Reservasi 2).
     */
    public function show(string $id)
    {
        // --- LOGIKA PENGAMBILAN DATA UNTUK DETAIL ---

        // Cari reservasi berdasarkan ID atau No. RM.
        $reservasi = Reservasi::with(['pasien', 'dokter', 'jadwal.poli'])
                            ->where('id', $id) // Coba cari berdasarkan ID
                            ->orWhere('no_pemeriksaan', $id) // Coba cari berdasarkan No RM jika ID tidak ditemukan
                            ->firstOrFail();

        // Mengirimkan data ke View 'reservasi.show'
        // 'reservasi.show' merujuk ke resources/views/reservasi/show.blade.php
        return view('reservasi.show', compact('reservasi')); 
    }
    
    // [ ... fungsi lain dihilangkan untuk brevity ... ]
}