<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\MpUser;
use Exception;

class AuthController extends Controller
{
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
                // --- DEBUGGING START ---
                // Cek paksa via email saja untuk memastikan data terbaca
                $debugEmail = MpUser::where('email', $validated['identifier'])->first();
                $debugNik = MpUser::where('nik', $validated['identifier'])->first();
                
                // Cek apakah identifier ini ada di tabel rekam_medis secara langsung?
                $debugRM = \App\Models\RekamMedis::where('rekam_medis', $validated['identifier'])->first();

                if ($debugEmail || $debugNik || $debugRM) {
                     $foundType = $debugEmail ? 'Email' : ($debugNik ? 'NIK' : 'RekamMedisTable');
                     return response()->json([
                        'success' => false, 
                        'message' => "DEBUG: Data ditemukan di $foundType! Tapi tidak terhubung ke User atau Query utama gagal.",
                        'debug_data' => $debugEmail ?? $debugNik ?? $debugRM
                     ], 404);
                }
                // --- DEBUGGING END ---

                return response()->json(['success' => false, 'message' => 'Akun tidak ditemukan'], 404);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json(['success' => false, 'message' => 'Password salah'], 401);
            }

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
                    'token'          => $token,
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