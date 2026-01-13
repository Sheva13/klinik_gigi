<?php

namespace App\Http\Controllers;

use App\Models\MpUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDataUserController extends Controller
{
    public function index(Request $request)
    {
        // Menampilkan user terbaru di atas
        $users = MpUser::with('rekamMedis')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('datausers.index', compact('users'));
    }

    public function show($id)
    {
        $user = MpUser::with(['rekamMedis', 'reservasi'])->findOrFail($id);
        return view('datausers.show', compact('user'));
    }

    public function edit($id)
    {
        $user = MpUser::with('rekamMedis')->findOrFail($id);
        return view('datausers.edit', compact('user'));
    }

    /**
     * UPDATE FULL (SENSITIF & NON-SENSITIF) + VERIFIKASI
     * Admin berhak mengoreksi nama/NIK jika user typo.
     */
    public function update(Request $request, $id)
    {
        $user = MpUser::with('rekamMedis')->findOrFail($id);

        // 1. Validasi Semua Data (Admin boleh edit semuanya sesuai KTP)
        $validated = $request->validate([
            // Data Akun (MpUser)
            'nama_pengguna'  => 'required|string|max:255',
            'email'          => 'nullable|email',
            'no_hp'          => 'nullable|string|max:20',
            'alamat'         => 'nullable|string',
            
            // Data Medis (RekamMedis) - Admin WAJIB cek ini
            'nama_lengkap'   => 'required|string|max:255', // Nama di RM
            'nik'            => 'required|numeric',         // NIK di RM
            'tanggal_lahir'  => 'required|date',
            'tempat_lahir'   => 'nullable|string',
            'jenis_kelamin'  => 'nullable|in:Laki-laki,Perempuan',
            'golongan_darah' => 'nullable|string|max:3',
            'pekerjaan'      => 'nullable|string',
            'status_nikah'   => 'nullable|integer', // Sesuaikan tipe data db (integer/string)

            // Data Verifikasi (Checkbox dari Admin)
            'verifikasi'     => 'required|boolean', // 1 = Terverifikasi, 0 = Belum
        ]);

        DB::beginTransaction(); // Pakai transaksi biar aman

        try {
            // 2. Update Tabel User (Akun Login)
            $user->update([
                'nama_pengguna' => $validated['nama_pengguna'],
                'email'         => $validated['email'],
                'no_hp'         => $validated['no_hp'],
                'alamat'        => $validated['alamat'],
                'nik'           => $validated['nik'], // Sync NIK di user juga
            ]);

            // 3. Update Tabel Rekam Medis (Data Inti)
            if ($user->rekamMedis) {
                $user->rekamMedis->update([
                    'nama'           => $validated['nama_lengkap'], // Nama sesuai KTP
                    'no_identitas'   => $validated['nik'],          // NIK sesuai KTP
                    'tempat_lahir'   => $validated['tempat_lahir'],
                    'tanggal_lahir'  => $validated['tanggal_lahir'],
                    'jenis_kelamin'  => $validated['jenis_kelamin'],
                    'alamat'         => $validated['alamat'],
                    'hp'             => $validated['no_hp'],
                    'pekerjaan'      => $validated['pekerjaan'],
                    'status_nikah'   => $validated['status_nikah'],
                    'golongan_darah' => $validated['golongan_darah'],
                    
                    // INI YANG PALING PENTING: UPDATE STATUS VERIFIKASI
                    'verifikasi'     => $validated['verifikasi'], 
                ]);
            }

            DB::commit();

            return redirect()
                ->route('datausers.show', $user->user_id)
                ->with('success', 'Data Pasien Berhasil Diverifikasi & Diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
}