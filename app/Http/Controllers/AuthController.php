<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MpUser;
use Carbon\Carbon;
use Exception;

class AuthController extends Controller
{
    /**
     * Generate unique user ID dengan format PSNYYYYMMDDXXX
     */
    private function generateUniqueUserId()
    {
        $tanggal = Carbon::now()->format('Ymd');
        do {
            $random = random_int(100, 999);
            $userId = "PSN{$tanggal}{$random}";
        } while (MpUser::where('user_id', $userId)->exists());

        return $userId;
    }

    /**
     * Register pasien baru atau lama
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipe_pasien'       => 'required|in:lama,baru',
                'rekam_medis_id'    => 'nullable|string',
                'nama_pengguna'     => 'nullable|string|max:100',
                'nik'               => 'nullable|string|max:20|unique:users,nik',
                'email'             => 'nullable|email|max:100|unique:users,email',
                'no_hp'             => 'nullable|string|max:20|unique:users,no_hp',
                'tanggal_lahir'     => 'nullable|date',
                'jenis_kelamin'     => 'nullable|in:Laki-laki,Perempuan',
                'password'          => 'required|string|min:6|confirmed',
            ]);

            if ($validated['tipe_pasien'] === 'lama') {
                $rekamMedis = DB::table('rekam_medis')
                    ->where('rekam_medis', $validated['rekam_medis_id'])
                    ->first();

                if (!$rekamMedis) {
                    return response()->json(['success' => false, 'message' => 'Nomor rekam medis tidak ditemukan'], 404);
                }

                $existingUser = MpUser::where('rekam_medis_id', $rekamMedis->id)->first();
                if ($existingUser) {
                    return response()->json(['success' => false, 'message' => 'Akun untuk rekam medis ini sudah terdaftar'], 409);
                }

                $user = MpUser::create([
                    'user_id'         => $this->generateUniqueUserId(),
                    'nama_pengguna'   => $rekamMedis->nama,
                    'nik'             => $rekamMedis->no_identitas,
                    'rekam_medis_id'  => $rekamMedis->id,
                    'tanggal_lahir'   => $rekamMedis->tanggal_lahir,
                    'jenis_kelamin'   => $rekamMedis->jenis_kelamin,
                    'no_hp'           => $rekamMedis->hp ?: ($validated['no_hp'] ?? null),
                    'email'           => $validated['email'] ?? null,
                    'password'        => Hash::make($validated['password']),
                ]);
            } else {
                $user = MpUser::create([
                    'user_id'         => $this->generateUniqueUserId(),
                    'nama_pengguna'   => $validated['nama_pengguna'],
                    'nik'             => $validated['nik'],
                    'rekam_medis_id'  => null,
                    'tanggal_lahir'   => $validated['tanggal_lahir'] ?? null,
                    'jenis_kelamin'   => $validated['jenis_kelamin'] ?? null,
                    'no_hp'           => $validated['no_hp'],
                    'email'           => $validated['email'],
                    'password'        => Hash::make($validated['password']),
                ]);
            }

            // ✅ Tambahan di bawah ini → generate token setelah register
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->update(['current_token' => $token]);

            return response()->json([
                'success' => true,
                'message' => $validated['tipe_pasien'] === 'lama'
                    ? 'Akun pasien lama berhasil dibuat'
                    : 'Registrasi pasien baru berhasil',
                'data' => [
                    'user_id' => $user->user_id,
                    'nama_pengguna' => $user->nama_pengguna,
                    'email' => $user->email,
                    'token' => $token, // 
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Register Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Login user (bisa pakai NIK / Email / Rekam Medis)
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'identifier' => 'required|string',
                'password'   => 'required|string',
            ]);

            $user = MpUser::where('nik', $validated['identifier'])
                ->orWhere('email', $validated['identifier'])
                ->orWhereHas('rekamMedis', function ($query) use ($validated) {
                    $query->where('rekam_medis', $validated['identifier']);
                })
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Akun tidak ditemukan'], 404);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json(['success' => false, 'message' => 'Password salah'], 401);
            }

            // ✅ Tambahan di bawah ini → generate token saat login
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->update(['current_token' => $token]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user_id'        => $user->user_id,
                    'nama_pengguna'  => $user->nama_pengguna,
                    'email'          => $user->email,
                    'rekam_medis_id' => $user->rekam_medis_id,
                    'token'          => $token, // ✅ kirim token
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Logout (hapus token Sanctum)
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                // 
                $user->tokens()->delete();
                $user->update(['current_token' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil, token dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal logout', 'error' => $e->getMessage()], 500);
        }
    }
}
