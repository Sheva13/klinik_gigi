<?php

namespace App\Http\Controllers;

use App\Models\MasterPromo; // Pastikan Model sudah dibuat
use Illuminate\Http\Request;

class PromoControllerWeb extends Controller
{
    public function index()
    {
        // Mengambil semua data promo
        $promos = MasterPromo::all(); 
        
        // Jika mau pagination: $promos = MasterPromo::paginate(10);
        
        return view('promo.index', compact('promos'));
    }
}