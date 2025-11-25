<?php

namespace App\Http\Controllers;

use App\Models\MasterDokter;
use App\Models\MasterSpesialis; // Pastikan model ini ada
use App\Models\MasterPoli;      // Pastikan model ini ada (opsional)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokterControllerWeb extends Controller
{
    // Cari function index() dan GANTI dengan kode berikut:

public function index(Request $request)
{
    // 1. Mulai Query Builder
    $query = MasterDokter::with('spesialis');

    // 2. Cek apakah ada input 'search' dari pengguna
    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        
        // Filter berdasarkan Nama, Gelar, STR, atau SIP
        $query->where(function($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('gelar', 'like', '%' . $search . '%')
              ->orWhere('dokter_str', 'like', '%' . $search . '%')
              ->orWhere('dokter_sip', 'like', '%' . $search . '%');
        });
    }

    // 3. Ambil data hasil filter
    $dokters = $query->get();

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
        // 1. Validasi (Diperbarui sesuai form & tabel migrasi)
        $request->validate([
            // Data Wajib
            'kode_dokter'       => 'required|string|max:15|unique:master_dokter,kode_dokter',
            'inisial'           => 'required|string|max:2', // Tambahan baru
            'nama'              => 'required|string|max:50',
            'gelar'             => 'required|string|max:50',
            'spesialisasi'      => 'required|integer', // ID Spesialis
            'hp'                => 'required|string|max:15',
            'alamat'            => 'required|string|max:50',
            'dokter_str'        => 'required|string|max:250',
            'file_foto'         => 'required|image|mimes:jpeg,png,jpg|max:2048',

            // Data Opsional (Nullable)
            'kode_poli'         => 'nullable|string|max:15',
            'dokter_str_mulai'  => 'nullable|date',
            'dokter_str_expire' => 'nullable|date',
            'dokter_sip'        => 'nullable|string|max:250',
            'dokter_sip_berlaku'=> 'nullable|date',
            'dokter_sip_expired'=> 'nullable|date',
        ]);

        // 2. Persiapkan Data
        $data = $request->all();

        // 3. Handle Upload Foto
        if ($request->hasFile('file_foto')) {
            $file = $request->file('file_foto');
            // Nama file: doc_TIMESTAMP.jpg
            $filename = 'doc_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/dokter', $filename, 'public');
            $data['file_foto'] = 'uploads/dokter/' . $filename;
        }

        // 4. Set Default Value untuk data kosong
        $data['tipe'] = $request->tipe ?? 1; // Default tipe 1
        
        // Atur tanggal default '1960-01-01' JIKA user tidak mengisi tanggal (agar tidak error di database)
        $data['dokter_str_mulai']   = $request->dokter_str_mulai ?? '1960-01-01';
        $data['dokter_str_expire']  = $request->dokter_str_expire ?? '1960-01-01';
        // SIP Tanggal (Opsional, biarkan null jika di database nullable, atau set default jika perlu)
        // Karena di migration sip_berlaku tipe string/varchar, kita biarkan apa adanya dari input date
        
        // 5. Simpan ke Database
        MasterDokter::create($data);

        return redirect()->route('dokter.index')->with('success', 'Dokter berhasil ditambahkan!');
    }

    public function destroy($id)
{
    // 1. Cari data dokter berdasarkan ID
    $dokter = MasterDokter::findOrFail($id);
    
    // 2. CEK RELASI (PENTING)
    // Jangan hapus dokter jika dia sudah memiliki Jadwal atau Reservasi
    // Ini mencegah error SQL Constraint (Integrity violation)
    if ($dokter->reservasi()->exists()) {
        return redirect()->back()->with('error', 'Gagal menghapus! Dokter ini memiliki data riwayat reservasi.');
    }

    if ($dokter->masterJadwal()->exists()) {
        return redirect()->back()->with('error', 'Gagal menghapus! Dokter ini masih memiliki jadwal aktif. Hapus jadwal terlebih dahulu.');
    }

    // 3. Hapus File Foto Fisik jika ada
    if ($dokter->file_foto && Storage::disk('public')->exists($dokter->file_foto)) {
        Storage::disk('public')->delete($dokter->file_foto);
    }

    // 4. Hapus data dari database
    $dokter->delete();

    // 5. Redirect dengan pesan sukses
    return redirect()->route('dokter.index')->with('success', 'Data dokter berhasil dihapus.');
}
// --- EDIT FORM ---
    public function edit($id)
    {
        $dokter = MasterDokter::findOrFail($id);
        
        // Ambil data pendukung untuk dropdown
        $spesialis = \App\Models\MasterSpesialis::all();
        $polis = \App\Models\MasterPoli::all();

        return view('dokter.edit', compact('dokter', 'spesialis', 'polis'));
    }

    // --- UPDATE PROCESS ---
    public function update(Request $request, $id)
    {
        $dokter = MasterDokter::findOrFail($id);

        // 1. Validasi
        // Perhatikan 'unique' untuk kode_dokter: abaikan ID dokter yang sedang diedit
        $request->validate([
            'kode_dokter'       => 'required|string|max:15|unique:master_dokter,kode_dokter,' . $id,
            'inisial'           => 'required|string|max:2',
            'nama'              => 'required|string|max:50',
            'gelar'             => 'required|string|max:50',
            'spesialisasi'      => 'required|integer',
            'hp'                => 'required|string|max:15',
            'alamat'            => 'required|string|max:50',
            'dokter_str'        => 'required|string|max:250',
            // Foto tidak wajib saat update (nullable)
            'file_foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Opsional
            'kode_poli'         => 'nullable|string|max:15',
            'dokter_str_mulai'  => 'nullable|date',
            'dokter_str_expire' => 'nullable|date',
            'dokter_sip'        => 'nullable|string|max:250',
            'dokter_sip_berlaku'=> 'nullable|date',
            'dokter_sip_expired'=> 'nullable|date',
        ]);

        // 2. Persiapkan Data
        $data = $request->except(['file_foto', '_token', '_method']);

        // 3. Handle Update Foto
        if ($request->hasFile('file_foto')) {
            $file = $request->file('file_foto');
            
            // Hapus foto lama jika ada & bukan default
            if ($dokter->file_foto && Storage::disk('public')->exists($dokter->file_foto)) {
                Storage::disk('public')->delete($dokter->file_foto);
            }

            // Upload foto baru
            $filename = 'doc_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/dokter', $filename, 'public');
            $data['file_foto'] = 'uploads/dokter/' . $filename;
        }

        // Handle Date Defaults (Jika null di form, pakai value lama atau default)
        $data['dokter_str_mulai']   = $request->dokter_str_mulai ?? '1960-01-01';
        $data['dokter_str_expire']  = $request->dokter_str_expire ?? '1960-01-01';

        // 4. Update Database
        $dokter->update($data);

        return redirect()->route('dokter.index')->with('success', 'Data dokter berhasil diperbarui!');
    }

}