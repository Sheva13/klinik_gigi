<?php

namespace App\Http\Controllers;

use App\Models\MpUser;
use App\Models\AdminUserAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserSensitiveController extends Controller
{
    public function edit($id)
    {
        $user = MpUser::with('rekamMedis')
            ->findOrFail($id);

        return view('datausers.edit-sensitive', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = MpUser::with('rekamMedis')
            ->findOrFail($id);

        $validated = $request->validate([
            // MpUser sensitif
            'nik' => 'required|string|max:20',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',

            // Rekam medis sensitif
            'no_identitas' => 'nullable|string|max:30',
            'tipe_identitas' => 'nullable|string|max:20',
            'tempat_lahir' => 'nullable|string|max:100',
            'rm_tanggal_lahir' => 'required|date',
            'rm_jenis_kelamin' => 'required|in:L,P',

            // Audit
            'alasan' => 'required|string|min:10',
        ]);

        /**
         * AUDIT LOG
         */
        AdminUserAuditLog::create([
            'admin_id' => Auth::id(),
            'user_id' => $user->user_id,
            'old_data' => [
                'mp_user' => $user->only([
                    'nik', 'tanggal_lahir', 'jenis_kelamin'
                ]),
                'rekam_medis' => optional($user->rekamMedis)->only([
                    'no_identitas',
                    'tipe_identitas',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'jenis_kelamin',
                ]),
            ],
            'new_data' => [
                'mp_user' => [
                    'nik' => $validated['nik'],
                    'tanggal_lahir' => $validated['tanggal_lahir'],
                    'jenis_kelamin' => $validated['jenis_kelamin'],
                ],
                'rekam_medis' => [
                    'no_identitas' => $validated['no_identitas'],
                    'tipe_identitas' => $validated['tipe_identitas'],
                    'tempat_lahir' => $validated['tempat_lahir'],
                    'tanggal_lahir' => $validated['rm_tanggal_lahir'],
                    'jenis_kelamin' => $validated['rm_jenis_kelamin'],
                ],
            ],
            'alasan' => $validated['alasan'],
        ]);

        /**
         * UPDATE DATA
         */
        $user->update([
            'nik' => $validated['nik'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
        ]);

        if ($user->rekamMedis) {
            $user->rekamMedis->update([
                'no_identitas' => $validated['no_identitas'],
                'tipe_identitas' => $validated['tipe_identitas'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['rm_tanggal_lahir'],
                'jenis_kelamin' => $validated['rm_jenis_kelamin'],
            ]);
        }

        return redirect()
            ->route('admin.users.show', $user->user_id)
            ->with('success', 'Data sensitif diperbarui & dicatat');
    }
}
