<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterDokter;
use App\Models\MasterPromo;
use App\Models\MasterJadwal;
use App\Models\Reservasi; // Asumsi ada model Reservasi
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Data Counter (Kartu Atas)
        $totalDokter = MasterDokter::count();
        $totalPromo = MasterPromo::count();
        
        // Contoh data dummy/real lainnya
        $reservasiHariIni = 12; // Ganti dengan Reservasi::whereDate('tgl_reservasi', now())->count();
        $homeCare = 5; // Ganti dengan logic Home Care kamu

        // 2. Data Grafik: Dokter dengan Jadwal Terbanyak
        // Mengambil Nama Dokter dan Jumlah Jadwalnya
        $chartData = MasterDokter::withCount('masterJadwal') // Pastikan relasi di model MasterDokter bernama 'masterJadwal'
                    ->orderBy('master_jadwal_count', 'desc') // Urutkan dari yang terbanyak
                    ->limit(10) // Ambil top 10 saja biar grafik rapi
                    ->get();

        $chartLabels = $chartData->pluck('nama'); // Nama dokter untuk Label X
        $chartValues = $chartData->pluck('master_jadwal_count'); // Jumlah jadwal untuk Data Y

        return view('dashboard', compact(
            'totalDokter', 
            'totalPromo', 
            'reservasiHariIni', 
            'homeCare',
            'chartLabels',
            'chartValues'
        ));
    }
}