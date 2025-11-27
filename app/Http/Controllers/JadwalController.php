<?php

namespace App\Http\Controllers;

use App\Models\MasterDokter;
use App\Models\MasterJadwal;
use App\Models\MasterPoli;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua dokter untuk list di sebelah kiri
        // Kita juga meload relasi spesialis agar bisa menampilkan gelar/spesialisasi
        $dokters = MasterDokter::with('spesialis')->get();

        // Ambil data Poli untuk dropdown di Modal Tambah Jadwal
        $polis = MasterPoli::all();

        // Cek apakah ada dokter yang dipilih dari parameter URL (misal: ?dokter_id=DR001)
        $selectedDokter = null;
        $jadwals = collect(); // Kosong default

        if ($request->has('dokter_id')) {
            $selectedDokter = MasterDokter::where('kode_dokter', $request->dokter_id)->first();
            
            if ($selectedDokter) {
                // Ambil jadwal milik dokter tersebut
                $jadwals = MasterJadwal::where('kode_dokter', $selectedDokter->kode_dokter)
                            ->with('poli') // Eager load poli
                            ->orderBy('hari', 'asc')
                            ->orderBy('jam_mulai', 'asc')
                            ->get();
            }
        } else {
            // Jika tidak ada yang dipilih, otomatis pilih dokter pertama jika ada
            if ($dokters->count() > 0) {
                $selectedDokter = $dokters->first();
                return redirect()->route('jadwal.index', ['dokter_id' => $selectedDokter->kode_dokter]);
            }
        }

        return view('jadwal.index', compact('dokters', 'selectedDokter', 'jadwals', 'polis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_dokter' => 'required|exists:master_dokter,kode_dokter',
            'kode_poli'   => 'required|exists:master_poli,kode_poli',
            'hari'        => 'required|integer|min:1|max:7',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
        ]);

        MasterJadwal::create($request->all());

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $jadwal = MasterJadwal::findOrFail($id);
        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus.');
    }
    // TAMBAHKAN INI: Method untuk mengambil data jadwal (JSON) untuk Modal Edit
    public function edit($id)
    {
        $jadwal = MasterJadwal::findOrFail($id);
        return response()->json($jadwal);
    }

    // TAMBAHKAN INI: Method untuk menyimpan perubahan (Update)
    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_poli'   => 'required|exists:master_poli,kode_poli',
            'hari'        => 'required|integer|min:1|max:7',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
        ]);

        $jadwal = MasterJadwal::findOrFail($id);
        
        // Kita gunakan update dengan except kode_dokter agar dokter tidak berubah
        // (karena di form edit tidak ada input kode_dokter)
        $jadwal->update($request->except(['_token', '_method', 'kode_dokter']));

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }
}