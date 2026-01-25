<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpUser;
use Illuminate\Support\Facades\Validator;
use App\Models\RekamMedis;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $rekam = RekamMedis::find($user->rekam_medis_id);

        $status_aktif = $rekam && $rekam->no_peserta
            ? 'aktif'
            : 'tidak aktif';

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil',
            'data' => [
                'user' => $user,
                'rekam_medis' => $rekam,
                'nama_asuransi' => $rekam->nama_asuransi ?? null,
                'no_peserta' => $rekam->no_peserta ?? null,
                'alamat' => $user->alamat ?? null,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_pengguna' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:users,email,' . $user->user_id . ',user_id',
            'tanggal_lahir' => 'sometimes|date',
            'alamat' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $dataUser = $request->only([
            'nama_pengguna',
            'no_hp',
            'email',
            'tanggal_lahir',
            'alamat'
        ]);

        $user->update($dataUser);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diupdate',
            'data' => [
                'user' => $user,
                'alamat' => $request->alamat ?? null,
            ],
        ]);
    }

    // ===============================
    // TAMBAHAN: UPLOAD FOTO PROFIL
    // ===============================
    public function uploadFoto(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi foto gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Hapus foto lama jika ada
        if ($user->file_foto && Storage::disk('public')->exists($user->file_foto)) {
            Storage::disk('public')->delete($user->file_foto);
        }

        // Simpan foto baru
        $path = $request->file('foto')->store('uploads', 'public');

        $user->update([
            'file_foto' => $path
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diupload',
            'data' => [
                'file_foto' => $path,
                'url' => asset('storage/' . $path)
            ]
        ]);
    }

    // ===============================
    // TAMBAHAN: AMBIL FOTO PROFIL
    // ===============================
    public function getFoto(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->file_foto) {
            return response()->json([
                'success' => false,
                'message' => 'Foto tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'file_foto' => $user->file_foto,
            'url' => asset('storage/' . $user->file_foto)
        ]);
    }

    // ===============================
    // TAMBAHAN: HAPUS FOTO PROFIL
    // ===============================
    public function deleteFoto(Request $request)
    {
        $user = $request->user();

        if ($user->file_foto && Storage::disk('public')->exists($user->file_foto)) {
            Storage::disk('public')->delete($user->file_foto);
        }

        $user->update([
            'file_foto' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil dihapus'
        ]);
    }
}
