<?php

namespace App\Http\Controllers;

use App\Models\MasterDokter;
use App\Models\MasterSpesialis; // Pastikan model ini ada
use App\Models\MasterPoli;      // Pastikan model ini ada (opsional)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokterControllerWeb extends Controller
{
    public function index()
    {
        $dokters = MasterDokter::with('spesialis')->get();
        return view('dokter.index', compact('dokters'));
    }

    public function create()
{
    // Ambil semua data spesialis untuk dropdown
    $spesialis = \App\Models\MasterSpesialis::all(); 
    
    // Ambil data poli (opsional, sesuai SQL kamu ada master_poli)
    $polis = \App\Models\MasterPoli::all(); 

    return view('dokter.create', compact('spesialis', 'polis'));
}

    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'nama'              => 'required|string|max:50',
            'kode_dokter'       => 'required|string|max:15|unique:master_dokter,kode_dokter',
            'gelar'             => 'required|string|max:50',
            'spesialisasi'      => 'required|integer', // Harus ID
            'file_foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'alamat'            => 'required|string|max:50',
            'hp'                => 'required|string|max:15',
            'dokter_str'        => 'required|string|max:250',
            // Validasi tanggal & sip opsional tapi disarankan
            'dokter_str_mulai'  => 'nullable|date',
            'dokter_str_expire' => 'nullable|date',
        ]);

        // 2. Persiapkan Data
        $data = $request->all();

        // 3. Handle Upload Foto (Hati-hati batas 55 karakter)
        if ($request->hasFile('file_foto')) {
            $file = $request->file('file_foto');
            
            // Generate nama file pendek: doc_TIMESTAMP.ext
            // Contoh: doc_17162833.jpg (sekitar 15-20 char, aman untuk limit 55)
            $filename = 'doc_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Simpan di folder public/uploads/dokter
            $file->storeAs('uploads/dokter', $filename, 'public');
            
            // Simpan path relatif di database
            $data['file_foto'] = 'uploads/dokter/' . $filename;
        }

        // Set default value jika kosong (sesuai migration)
        $data['tipe'] = $request->tipe ?? 1;
        $data['dokter_str_mulai'] = $request->dokter_str_mulai ?? '1960-01-01';
        $data['dokter_str_expire'] = $request->dokter_str_expire ?? '1960-01-01';

        // 4. Simpan ke Database
        MasterDokter::create($data);

        return redirect()->route('dokter.index')->with('success', 'Dokter berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $dokter = MasterDokter::findOrFail($id);
        
        // Hapus file foto fisik jika ada
        if ($dokter->file_foto && Storage::disk('public')->exists($dokter->file_foto)) {
            Storage::disk('public')->delete($dokter->file_foto);
        }

        $dokter->delete();
        return redirect()->route('dokter.index')->with('success', 'Data dokter berhasil dihapus.');
    }
}