<?php

namespace App\Http\Controllers;

use App\Models\MasterDokter;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Storage;

class DokterController extends Controller
{
    /**
     * Mendapatkan daftar dokter dengan data minimal.
     * PERUBAHAN 1: Tambahkan (Request $request)
     */
    public function index(Request $request) 
    {
        try {

            $query = MasterDokter::with('spesialis');

            $query->when($request->input('search'), function ($q, $search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhereHas('spesialis', function ($sq) use ($search) {
                      $sq->where('nama', 'LIKE', "%{$search}%");
                  });
            });

            $dokters = $query->get();

            $data = $dokters->map(function ($dokter) {
                
                $fotoUrl = null;
                // Ini adalah logika yang benar untuk mengambil URL foto
                if (!empty($dokter->foto_profil)) {
                    $fotoUrl = asset(Storage::url($dokter->foto_profil));
                }

                return [
                    'dokter_id' => $dokter->id, // Menggunakan 'id' (primary key)
                    'nama_dokter' => $dokter->nama ?? '', 
                    'spesialisasi' => $dokter->spesialis?->nama ?? '', 
                    'foto_profil' => $fotoUrl,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            // Jika terjadi error, kirim pesan error 500
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // mengmbil data dokter berdasarkan ID.
            $dokter = MasterDokter::with(['masterPoli', 'masterJadwal']) 
                                ->find($id);

            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Dokter tidak ditemukan',
                ], 404);
            }
            
            // proses foto
            $fotoUrl = null;
            if (!empty($dokter->foto_profil)) { 
                $fotoUrl = asset(Storage::url($dokter->foto_profil));
            }

            // Susun data respons
            $data = [
                'id' => $dokter->id, 
                'nama' => $dokter->nama,
                'foto' => $fotoUrl, 
                'spesialisasi' => $dokter->spesialisasi, 
                'masterPoli' => $dokter->masterPoli,    
                'masterJadwal' => $dokter->masterJadwal, 
            ];

            // Kembalikan data sebagai JSON
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Method untuk meng-upload foto profil (Hanya Admin).
     */
    public function uploadFotoProfil(Request $request, $id)
    {
        // 1. Validasi request.
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 2. Cari dokter berdasarkan ID
        $dokter = MasterDokter::find($id);
        if (!$dokter) {
            return response()->json(['message' => 'Dokter tidak ditemukan'], 404);
        }

        // 3. Cek jika ada file 'foto'
        if ($request->hasFile('foto')) {
            
            // 4. Hapus foto lama
            if ($dokter->foto_profil) {
                Storage::disk('public')->delete($dokter->foto_profil);
            }

            // 5. Simpan file baru
            $path = $request->file('foto')->store('uploads', 'public');

            // 6. Simpan path baru ke database
            $dokter->foto_profil = $path;
            $dokter->save();
            
            // 7. Buat URL lengkap untuk respons
            $urlLengkap = asset(Storage::url($path));

            return response()->json([
                'message' => 'Upload foto berhasil',
                'path' => $path,
                'url' => $urlLengkap
            ], 200, [], JSON_UNESCAPED_SLASHES);

        }

        return response()->json(['message' => 'Tidak ada file yang di-upload'], 400);
    }
}