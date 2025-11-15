<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpUser;
use Illuminate\Support\Facades\Validator;
use App\Models\RekamMedis;

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

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil',
            'data' => $user
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

        // Validasi input yang diperbolehkan
        $validator = Validator::make($request->all(), [
            'nama_pengguna' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:users,email,' . $user->user_id . ',user_id',
            'tanggal_lahir' => 'sometimes|date',
            'alamat' => 'sometimes|string|max:255', // dari rekam medis
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update tabel users
        $dataUser = $request->only([
            'nama_pengguna',
            'no_hp',
            'email',
            'tanggal_lahir'
        ]);

        $user->update($dataUser);

        // Update alamat di tabel rekam_medis
        if ($request->filled('alamat')) {
            $rekam = RekamMedis::find($user->rekam_medis_id);

            if ($rekam) {
                $rekam->alamat = $request->alamat;
                $rekam->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'user' => $user,
                'rekam_medis' => RekamMedis::find($user->rekam_medis_id)
            ]
        ]);
    }
}