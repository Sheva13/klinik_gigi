<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MpUser; // Pastikan model ini merujuk ke tabel 'users'
use App\Models\RekamMedis; // Model untuk tabel 'rekam_medis'
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class PasienController extends Controller
{
    /**
     * Mengambil data detail pasien yang sedang login.
     */
    public function me(Request $request)
    {
        try {
            // Mengambil user yang terautentikasi via token Sanctum
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
            }

            // Memuat relasi rekamMedis
            // Pastikan Anda sudah mendefinisikan relasi 'rekamMedis' di model MpUser
            $user->load('rekamMedis');

            // Hitung umur
            $umur = 0;
            if ($user->tanggal_lahir) {
                // Gunakan tanggal_lahir dari tabel 'users'
                $umur = Carbon::parse($user->tanggal_lahir)->age;
            }

            // Ambil nomor rekam medis dari relasi
            $rekamMedisNumber = $user->rekamMedis ? $user->rekamMedis->rekam_medis : 'N/A';
            
            return response()->json([
                'success' => true,
                'message' => 'Data pasien berhasil diambil',
                'data' => [
                    // 'nama' di Flutter (pasien_model) diharapkan dari 'nama_pengguna' di Laravel
                    'nama'          => $user->nama_pengguna, 
                    'umur'          => $umur, // Flutter model (pasien_model.dart) mengharapkan int
                    'jenis_kelamin' => $user->jenis_kelamin, // 'Laki-laki' atau 'Perempuan'
                    'rekam_medis'   => $rekamMedisNumber,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Get Pasien Me Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server', 'error' => $e->getMessage()], 500);
        }
    }
}