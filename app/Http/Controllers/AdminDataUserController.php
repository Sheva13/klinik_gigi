<?php

namespace App\Http\Controllers;

use App\Models\MpUser;
use Illuminate\Http\Request;

class AdminDataUserController extends Controller
{
    public function index(Request $request)
    {
        $users = MpUser::with('rekamMedis')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('datausers.index', compact('users'));
    }

    /**
     * SHOW - SEMUA DATA (READ ONLY)
     */
    public function show($id)
    {
        $user = MpUser::with(['rekamMedis', 'reservasi'])
            ->findOrFail($id);

        return view('datausers.show', compact('user'));
    }

    /**
     * EDIT BIASA
     * - Semua data ditampilkan
     * - Data sensitif READ ONLY
     */
    public function edit($id)
    {
        $user = MpUser::with('rekamMedis')
            ->findOrFail($id);

        return view('datausers.edit', compact('user'));
    }

    /**
     * UPDATE DATA NON-SENSITIF SAJA
     */
    public function update(Request $request, $id)
    {
        $user = MpUser::with('rekamMedis')
            ->findOrFail($id);

        /**
         * MpUser NON SENSITIF
         */
        $userData = $request->validate([
            'nama_pengguna' => 'required|string|max:255',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        /**
         * Rekam Medis NON SENSITIF
         */
        $rekamMedisData = $request->validate([
            'status_nikah' => 'nullable|string',
            'pekerjaan' => 'nullable|string',
            'hp' => 'nullable|string|max:20',
            'golongan_darah' => 'nullable|string|max:3',
            'nama_wali' => 'nullable|string',
            'hubungan_wali' => 'nullable|string',
            'hp_wali' => 'nullable|string',
            'jenis_pasien' => 'nullable|string',
            'no_peserta' => 'nullable|string',
            'nama_asuransi' => 'nullable|string',
        ]);

        $user->update($userData);

        if ($user->rekamMedis) {
            $user->rekamMedis->update($rekamMedisData);
        }

        return redirect()
            ->route('datausers.show', $user->user_id)
            ->with('success', 'Data non-sensitif berhasil diperbarui');
    }
}
