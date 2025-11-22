<?php

namespace App\Http\Controllers;

use App\Models\MasterPromo; // Pastikan Model sudah dibuat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromoControllerWeb extends Controller
{
    public function index()
    {
        // Mengambil semua data promo
        $promos = MasterPromo::all(); 
        
        // Jika mau pagination: $promos = MasterPromo::paginate(10);
        
        return view('promo.index', compact('promos'));
    }
    // --- TAMBAHKAN FUNCTION INI ---
    public function destroy($id)
    {
        // 1. Cari data promo berdasarkan ID
        $promo = MasterPromo::findOrFail($id);

        // 2. (Opsional) Hapus file gambar jika ada
        // Pastikan path-nya sesuai dengan filesystem Anda
        if ($promo->gambar_banner && Storage::exists('public/' . $promo->gambar_banner)) {
            Storage::delete('public/' . $promo->gambar_banner);
        }

        // 3. Hapus data dari database
        $promo->delete();

        // 4. Kembali ke halaman promo dengan pesan sukses
        return redirect()->route('promo.index')->with('success', 'Promo berhasil dihapus!');
    }
}