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
    // 1. Menampilkan Form Tambah
    public function create()
    {
        return view('promo.create');
    }

    // 2. Menyimpan Data ke Database
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'judul_promo'     => 'required|string|max:255',
            'deskripsi'       => 'required|string',
            'gambar_banner'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $path = null;

        // Proses Upload Gambar
        if ($request->hasFile('gambar_banner')) {
            // Simpan ke folder 'public/promos'
            // Pastikan sudah jalankan: php artisan storage:link
            $path = $request->file('gambar_banner')->store('promos', 'public');
        }

        // Simpan ke Database
        MasterPromo::create([
            'judul_promo'     => $request->judul_promo,
            'deskripsi'       => $request->deskripsi,
            'gambar_banner'   => $path,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        return redirect()->route('promo.index')->with('success', 'Promo berhasil ditambahkan!');
    }
    // 1. Tampilkan Form Edit
    public function edit($id)
    {
        $promo = MasterPromo::findOrFail($id);
        return view('promo.edit', compact('promo'));
    }

    // 2. Proses Update Data
    public function update(Request $request, $id)
    {
        $promo = MasterPromo::findOrFail($id);

        // Validasi (Gambar tidak wajib/nullable saat edit)
        $request->validate([
            'judul_promo'     => 'required|string|max:255',
            'deskripsi'       => 'required|string',
            'gambar_banner'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', 
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $dataToUpdate = [
            'judul_promo'     => $request->judul_promo,
            'deskripsi'       => $request->deskripsi,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ];

        // Cek jika ada upload gambar baru
        if ($request->hasFile('gambar_banner')) {
            // 1. Hapus gambar lama jika ada
            if ($promo->gambar_banner && Storage::exists('public/' . $promo->gambar_banner)) {
                Storage::delete('public/' . $promo->gambar_banner);
            }
            
            // 2. Upload gambar baru
            $path = $request->file('gambar_banner')->store('promos', 'public');
            $dataToUpdate['gambar_banner'] = $path;
        }

        $promo->update($dataToUpdate);

        return redirect()->route('promo.index')->with('success', 'Promo berhasil diperbarui!');
    }
}