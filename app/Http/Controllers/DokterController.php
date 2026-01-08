<?php

namespace App\Http\Controllers;

use App\Models\MasterDokter;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Storage;

class DokterController extends Controller
{
    /**
     * Mendapatkan daftar dokter dengan data minimal.
     */
    public function index(Request $request) 
    {
        try {
            $query = MasterDokter::with(['spesialis', 'masterPoli']);

            $query->when($request->input('search'), function ($q, $search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhereHas('spesialis', function ($sq) use ($search) {
                      $sq->where('nama', 'LIKE', "%{$search}%");
                  });
            });

            $dokters = $query->get();

            $data = $dokters->map(function ($dokter) {
                $fotoUrl = null;
                if (!empty($dokter->foto_profil)) {
                    $fotoUrl = asset(Storage::url($dokter->foto_profil));
                }

                // Robust check for Poli Name
                $poliNama = $dokter->masterPoli->nama_poli ?? $dokter->masterPoli->nama ?? 'Poli Gigi';

                // Robust check for Spesialisasi Name (ensure we get the name, not ID)
                $spesialisasiName = $dokter->spesialis->nama ?? null;
                $spesialisasi = $spesialisasiName ?? $this->getSpesialisasiName($dokter->spesialisasi);

                return [
                    'dokter_id' => $dokter->id,
                    'nama_dokter' => $dokter->nama ?? '',
                    'spesialisasi' => $spesialisasi,
                    'poli_nama' => $poliNama,
                    'foto_profil' => $fotoUrl,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data dokter: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Detail dokter.
     */
    public function show($id)
    {
        try {
            $dokter = MasterDokter::with(['masterPoli', 'masterJadwal', 'spesialis'])
                        ->find($id);

            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Dokter tidak ditemukan',
                ], 404);
            }
            
            $fotoUrl = null;
            if (!empty($dokter->foto_profil)) { 
                $fotoUrl = asset(Storage::url($dokter->foto_profil));
            }

            $data = [
                'id' => $dokter->id, 
                'nama' => $dokter->nama,
                'foto' => $fotoUrl,
                'spesialisasi' => $dokter->spesialis->nama ?? $this->getSpesialisasiName($dokter->spesialisasi), 
                'masterPoli' => $dokter->masterPoli,
                'masterJadwal' => $dokter->masterJadwal,
            ];

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
     * Upload foto profil dokter.
     */
    public function uploadFotoProfil(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $dokter = MasterDokter::find($id);
        if (!$dokter) {
            return response()->json(['message' => 'Dokter tidak ditemukan'], 404);
        }

        if ($request->hasFile('foto')) {
            if ($dokter->foto_profil) {
                Storage::disk('public')->delete($dokter->foto_profil);
            }

            $path = $request->file('foto')->store('uploads', 'public');
            $dokter->foto_profil = $path;
            $dokter->save();
            
            $urlLengkap = asset(Storage::url($path));

            return response()->json([
                'message' => 'Upload foto berhasil',
                'path' => $path,
                'url' => $urlLengkap
            ], 200, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json(['message' => 'Tidak ada file yang di-upload'], 400);
    }


    // =========================================
    // 🔹 KONVERSI ANGKA HARI → NAMA HARI
    // =========================================
    private function getSpesialisasiName($val)
    {
        // Jika sudah berupa nama (bukan angka), kembalikan langsung
        if (!is_numeric($val)) {
            return $val;
        }

        // Map Fallback jika relasi database gagal detailnya
        $map = [
            1 => 'Dokter Gigi Umum',
            2 => 'Spesialis Ortodonti',
            3 => 'Spesialis Bedah Mulut',
            4 => 'Spesialis Konservasi Gigi', 
            5 => 'Spesialis Periodonsia',
            6 => 'Spesialis Prostodonsia',
            7 => 'Spesialis Penyakit Mulut',
            8 => 'Spesialis Kedokteran Gigi Anak'
        ];

        return $map[$val] ?? 'Dokter Spesialis';
    }

    private function convertHari($angka)
    {
        $hariMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $hariMap[$angka] ?? '-';
    }


    /**
     * Ambil Jadwal Praktek Semua Dokter
     */
    public function getJadwalPraktek()
    {
        try {
            $dokters = MasterDokter::with(['masterJadwal', 'masterPoli', 'spesialis'])->get();

            $data = $dokters->map(function ($dokter) {

                $fotoUrl = null;
                if (!empty($dokter->foto_profil)) {
                    $fotoUrl = asset(Storage::url($dokter->foto_profil));
                }

                return [
                    'dokter_id' => $dokter->id,
                    'nama_dokter' => $dokter->nama,
                    'spesialisasi' => $dokter->spesialis?->nama ?? '',
                    'poli' => $dokter->masterPoli->nama ?? '-',
                    'foto_profil' => $fotoUrl,

                    'jadwal' => $dokter->masterJadwal->map(function ($j) {
                        return [
                            'hari' => $this->convertHari($j->hari),
                            'jam_mulai' => $j->jam_mulai,
                            'jam_selesai' => $j->jam_selesai,
                        ];
                    })
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil jadwal: ' . $e->getMessage()
            ], 500);
        }
    }
}
