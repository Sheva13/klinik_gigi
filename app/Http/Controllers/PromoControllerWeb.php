<?php

namespace App\Http\Controllers;

use App\Models\MasterPromo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromoControllerWeb extends Controller
{
    public function index(Request $request)
    {
        $query = MasterPromo::query();

        if ($request->has('target') && $request->target != '') {
            $query->where('target_transaksi', $request->target);
        }

        $promos = $query->orderBy('id', 'desc')->get();
        return view('promo.index', compact('promos'));
    }

    public function create()
    {
        return view('promo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_promo'     => 'required|string|max:255',
            'deskripsi'       => 'required|string',
            'gambar_banner'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', 
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            // Validasi Kolom Baru
            'harga_poin'      => 'required|integer|min:0',
            'nilai_potongan'  => 'required|numeric|min:0',
            'limit_per_user'  => 'required|integer|min:1',
            'target_transaksi' => 'required|in:booking,pelunasan,semua',
        ]);

        $path = null;
        if ($request->hasFile('gambar_banner')) {
            $path = $request->file('gambar_banner')->store('promos', 'public');
        }

        MasterPromo::create([
            'judul_promo'     => $request->judul_promo,
            'deskripsi'       => $request->deskripsi,
            'gambar_banner'   => $path,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            // Simpan Kolom Baru
            'harga_poin'      => $request->harga_poin,
            'nilai_potongan'  => $request->nilai_potongan,
            'limit_per_user'  => $request->limit_per_user,
            'target_transaksi' => $request->target_transaksi,
        ]);

        return redirect()->route('promo.index')->with('success', 'Promo berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $promo = MasterPromo::findOrFail($id);
        return view('promo.edit', compact('promo'));
    }

    public function update(Request $request, $id)
    {
        $promo = MasterPromo::findOrFail($id);

        $request->validate([
            'judul_promo'     => 'required|string|max:255',
            'deskripsi'       => 'required|string',
            'gambar_banner'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', 
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            // Validasi Kolom Baru
            'harga_poin'      => 'required|integer|min:0',
            'nilai_potongan'  => 'required|numeric|min:0',
            'limit_per_user'  => 'required|integer|min:1',
            'target_transaksi' => 'required|in:booking,pelunasan,semua',
        ]);

        $dataToUpdate = $request->except(['gambar_banner', '_token', '_method']);

        if ($request->hasFile('gambar_banner')) {
            if ($promo->gambar_banner && Storage::exists('public/' . $promo->gambar_banner)) {
                Storage::delete('public/' . $promo->gambar_banner);
            }
            $dataToUpdate['gambar_banner'] = $request->file('gambar_banner')->store('promos', 'public');
        }

        $promo->update($dataToUpdate);

        return redirect()->route('promo.index')->with('success', 'Promo berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $promo = MasterPromo::findOrFail($id);
        if ($promo->gambar_banner && Storage::exists('public/' . $promo->gambar_banner)) {
            Storage::delete('public/' . $promo->gambar_banner);
        }
        $promo->delete();
        return redirect()->route('promo.index')->with('success', 'Promo berhasil dihapus!');
    }
}