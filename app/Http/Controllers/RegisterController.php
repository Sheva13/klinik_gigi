<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MpUser;
use Carbon\Carbon;
use Exception;

class RegisterController extends Controller
{
    /**
     * Register pasien baru atau lama
     * Menyimpan ke pending_registrations sementara sebelum OTP diverifikasi
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipe_pasien'       => 'required|in:lama,baru',
                'rekam_medis_id'    => 'nullable|string',
                'nama_pengguna'     => 'nullable|string|max:100',
                'nik'               => 'nullable|string|max:20',
                'email'             => 'required|email|max:100',
                'no_hp'             => 'nullable|string|max:20',
                'tanggal_lahir'     => 'nullable|date',
                'jenis_kelamin'     => 'nullable|in:Laki-laki,Perempuan',
                'alamat'            => 'nullable|string|max:1000',
                'password'          => 'required|string|min:8|confirmed',
            ]);

            // Cek apakah email sudah terdaftar di mp_users
            $existingUser = MpUser::where('email', $validated['email'])->first();
            if ($existingUser) {
                return response()->json(['success' => false, 'message' => 'Email sudah terdaftar'], 409);
            }

            // Cek apakah email sudah ada di pending_registrations
            $existingPending = DB::table('pending_registrations')
                ->where('email', $validated['email'])
                ->first();

            if ($existingPending) {
                // Update data yang sudah ada di pending_registrations
                DB::table('pending_registrations')
                    ->where('email', $validated['email'])
                    ->update([
                        'tipe_pasien'       => $validated['tipe_pasien'],
                        'rekam_medis_id'    => $validated['rekam_medis_id'] ?? null,
                        'nama_pengguna'     => $validated['nama_pengguna'] ?? null,
                        'nik'               => $validated['nik'] ?? null,
                        'no_hp'             => $validated['no_hp'] ?? null,
                        'tanggal_lahir'     => $validated['tanggal_lahir'] ?? null,
                        'jenis_kelamin'     => $validated['jenis_kelamin'] ?? null,
                        'alamat'            => $validated['alamat'] ?? null,
                        'password'          => Hash::make($validated['password']),
                        'updated_at'        => now(),
                    ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data registrasi diperbarui, silakan verifikasi OTP',
                    'data' => [
                        'pending_registration_id' => $existingPending->id,
                        'email' => $validated['email'],
                    ],
                ], 200);
            } else {
                // Simpan ke pending_registrations sementara
                $pendingRegistrationId = DB::table('pending_registrations')->insertGetId([
                    'tipe_pasien'       => $validated['tipe_pasien'],
                    'rekam_medis_id'    => $validated['rekam_medis_id'] ?? null,
                    'nama_pengguna'     => $validated['nama_pengguna'] ?? null,
                    'nik'               => $validated['nik'] ?? null,
                    'email'             => $validated['email'],
                    'no_hp'             => $validated['no_hp'] ?? null,
                    'tanggal_lahir'     => $validated['tanggal_lahir'] ?? null,
                    'jenis_kelamin'     => $validated['jenis_kelamin'] ?? null,
                    'alamat'            => $validated['alamat'] ?? null,
                    'password'          => Hash::make($validated['password']),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi sementara berhasil, silakan verifikasi OTP',
                    'data' => [
                        'pending_registration_id' => $pendingRegistrationId,
                        'email' => $validated['email'],
                    ],
                ], 201);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Register Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server', 'error' => $e->getMessage()], 500);
        }
    }
}