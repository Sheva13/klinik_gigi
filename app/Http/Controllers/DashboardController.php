<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterDokter;
use App\Models\MasterPromo;
use App\Models\MasterJadwal;
use App\Models\Reservasi; 
use App\Models\HomeCareReservasi; // 1. TAMBAHKAN IMPORT MODEL INI
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Data Counter (Kartu Atas)
        $totalDokter = MasterDokter::count();
        $totalPromo = MasterPromo::count();
        
        $reservasiHariIni = Reservasi::whereDate('tanggal_pesan', now())->count();

        // 2. PERBAIKAN LOGIC HOME CARE
        // Mengambil jumlah data Home Care khusus "Bulan Ini" sesuai label di dashboard
        $homeCare = HomeCareReservasi::whereMonth('tanggal_pesan', now()->month)
                        ->whereYear('tanggal_pesan', now()->year)
                        ->count();

        // 3. Data Grafik: Dokter dengan Jadwal Terbanyak
        $chartData = MasterDokter::withCount('masterJadwal') 
                    ->orderBy('master_jadwal_count', 'desc') 
                    ->limit(10) 
                    ->get();

        $chartLabels = $chartData->pluck('nama'); 
        $chartValues = $chartData->pluck('master_jadwal_count'); 

        return view('dashboard', compact(
            'totalDokter', 
            'totalPromo', 
            'reservasiHariIni', 
            'homeCare', // Variabel ini sekarang sudah dinamis
            'chartLabels',
            'chartValues'
        ));
    }
}