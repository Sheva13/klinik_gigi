<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    /**
     * Mendapatkan daftar dokter dengan data minimal.
     */
    public function index()
    {
        $dokters = Dokter::select([
            'dokter_id', 
            'nama_dokter', 
            'spesialisasi', 
            'foto_profil'
        ])->get(); 

        // Base URL otomatis mengikuti konfigurasi Laravel
        $baseUrl = asset('');

        $data = $dokters->map(function ($dokter) use ($baseUrl) {
            $dokterArray = $dokter->toArray();

            if (!empty($dokterArray['foto_profil'])) {
                // Hapus karakter tak terlihat seperti \r, \n, atau spasi
                $path = trim($dokterArray['foto_profil']);

                // Buat URL lengkap ke gambar di folder public/uploads
                $dokterArray['foto_profil'] = $baseUrl . $path;
            }

            return $dokterArray;
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}
