<?php

namespace App\Http\Controllers;

// 1. Ganti model 'Dokter' menjadi 'MasterDokter'
use App\Models\MasterDokter;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    /**
     * Mendapatkan daftar dokter dengan data minimal.
     */
    public function index()
    {
        try {
            // 2. Query model MasterDokter dan ambil relasi 'spesialis'
            // Ini akan mengambil data dari tabel 'master_dokter'
            $dokters = MasterDokter::with('spesialis')->get();

            // 3. Tentukan base URL untuk foto
            // 'asset('')' akan menghasilkan http://127.0.0.1:8000
            $baseUrl = asset('');

            $data = $dokters->map(function ($dokter) use ($baseUrl) {
                
                // 4. Buat URL foto yang lengkap
                $fotoUrl = null;
                // Asumsi Anda akan menambahkan kolom 'foto_profil' ke tabel 'master_dokter'
                if (!empty($dokter->foto_profil)) {
                    $path = trim($dokter->foto_profil);
                    // Pastikan path dimulai dengan 'uploads/'
                    if (!str_starts_with($path, 'uploads/')) {
                         $path = 'uploads/' . $path;
                    }
                    $fotoUrl = $baseUrl . '/' . $path;
                }

                // 5. Format data agar SESUAI dengan 'dokter_model.dart' di Flutter
                return [
                    'dokter_id' => $dokter->kode_dokter, // Flutter mengharapkan 'dokter_id', kita berikan 'kode_dokter'
                    'nama_dokter' => $dokter->nama, // Flutter mengharapkan 'nama_dokter', kita berikan 'nama'
                    'spesialisasi' => $dokter->spesialis ? $dokter->spesialis->nama : 'Spesialis', // Ambil 'nama' dari relasi spesialis
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
}