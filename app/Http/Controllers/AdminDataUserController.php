<?php

namespace App\Http\Controllers;

use App\Models\MpUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDataUserController extends Controller
{
    public function index()
    {
        $users = MpUser::with('rekamMedis')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('datausers.index', compact('users'));
    }

    public function show($id)
    {
        $user = MpUser::with(['rekamMedis', 'reservasi'])
            ->findOrFail($id);

        return view('datausers.show', compact('user'));
    }

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

        $validated = $request->validate([
            // MpUser (non-sensitif)
            'nama_pengguna' => 'required|string|max:255',
            'email'         => 'nullable|email',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',

            // Rekam Medis non-sensitif
            'status_nikah'   => 'nullable|string',
            'pekerjaan'      => 'nullable|string',
            'hp'             => 'nullable|string|max:20',
            'golongan_darah' => 'nullable|string|max:3',
            'nama_wali'      => 'nullable|string|max:255',
            'hubungan_wali'  => 'nullable|string|max:100',
            'hp_wali'        => 'nullable|string|max:20',
            'jenis_pasien'   => 'nullable|string|max:100',
            'no_peserta'     => 'nullable|string|max:100',
            'nama_asuransi'  => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($user, $validated) {

            // Update akun user
            $user->update([
                'nama_pengguna' => $validated['nama_pengguna'],
                'email'         => $validated['email'],
                'no_hp'         => $validated['no_hp'],
                'alamat'        => $validated['alamat'],
            ]);

            // Update rekam medis (non-sensitif)
            if ($user->rekamMedis) {
                $user->rekamMedis->update([
                    'alamat'         => $validated['alamat'],
                    'hp'             => $validated['hp'] ?? $validated['no_hp'],
                    'status_nikah'   => $validated['status_nikah'],
                    'pekerjaan'      => $validated['pekerjaan'],
                    'golongan_darah' => $validated['golongan_darah'],
                    'nama_wali'      => $validated['nama_wali'],
                    'hubungan_wali'  => $validated['hubungan_wali'],
                    'hp_wali'        => $validated['hp_wali'],
                    'jenis_pasien'   => $validated['jenis_pasien'],
                    'no_peserta'     => $validated['no_peserta'],
                    'nama_asuransi'  => $validated['nama_asuransi'],
                ]);
            }
        });

        return redirect()
            ->route('admin.users.show', $user->user_id)
            ->with('success', 'Data non-sensitif berhasil diperbarui');
    }
}
