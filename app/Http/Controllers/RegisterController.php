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

            // Mode demo: OTP dimatikan, akun langsung dibuat dan aktif.
            if (config('otp.disabled', false)) {
                DB::table('pending_registrations')->where('email', $validated['email'])->delete();

                DB::beginTransaction();
                try {
                    [$user, $finalNoRM] = $this->createAccount($validated);

                    $token = $user->createToken('auth_token')->plainTextToken;
                    $user->update(['current_token' => $token]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Registrasi berhasil, akun langsung aktif',
                        'otp_required' => false,
                        'data' => [
                            'user_id' => $user->user_id,
                            'nama_pengguna' => $user->nama_pengguna,
                            'no_rm' => $finalNoRM,
                            'email' => $user->email,
                            'token' => $token,
                        ],
                    ], 201);
                } catch (\RuntimeException $e) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error('Register (OTP disabled) Error: ' . $e->getMessage());
                    return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server', 'error' => $e->getMessage()], 500);
                }
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

    private function createAccount(array $validated): array
    {
        if ($validated['tipe_pasien'] === 'lama') {
            $rekamMedis = DB::table('rekam_medis')
                ->where('rekam_medis', $validated['rekam_medis_id'] ?? '')
                ->first();

            if (!$rekamMedis) {
                throw new \RuntimeException('Nomor rekam medis tidak ditemukan');
            }

            $existingUser = MpUser::where('rekam_medis_id', $rekamMedis->id)->first();
            if ($existingUser) {
                throw new \RuntimeException('Akun untuk rekam medis ini sudah terdaftar');
            }

            $user = MpUser::create([
                'user_id' => $this->generateUniqueUserId(),
                'nama_pengguna' => $rekamMedis->nama,
                'nik' => $rekamMedis->no_identitas,
                'rekam_medis_id' => $rekamMedis->id,
                'tanggal_lahir' => $rekamMedis->tanggal_lahir,
                'jenis_kelamin' => $rekamMedis->jenis_kelamin,
                'no_hp' => $rekamMedis->hp ?: ($validated['no_hp'] ?? null),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'alamat' => $rekamMedis->alamat,
            ]);

            return [$user, $rekamMedis->rekam_medis];
        }

        $newNoRM = $this->generateNoRM();

        $rekamMedisId = DB::table('rekam_medis')->insertGetId([
            'rekam_medis' => $newNoRM,
            'nama' => $validated['nama_pengguna'],
            'no_identitas' => $validated['nik'] ?? null,
            'hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? '-',
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'verifikasi' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = MpUser::create([
            'user_id' => $this->generateUniqueUserId(),
            'nama_pengguna' => $validated['nama_pengguna'],
            'nik' => $validated['nik'] ?? null,
            'rekam_medis_id' => $rekamMedisId,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'email' => $validated['email'],
            'alamat' => $validated['alamat'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        return [$user, $newNoRM];
    }

    private function generateUniqueUserId(): string
    {
        $tanggal = Carbon::now()->format('Ymd');

        do {
            $random = random_int(100, 999);
            $userId = "PSN{$tanggal}{$random}";
        } while (MpUser::where('user_id', $userId)->exists());

        return $userId;
    }

    private function generateNoRM(): string
    {
        $dateCode = Carbon::now()->format('ymd');
        $prefix = 'RM' . $dateCode;

        do {
            $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $finalRM = $prefix . $random;
            $exists = DB::table('rekam_medis')->where('rekam_medis', $finalRM)->exists();
        } while ($exists);

        return $finalRM;
    }
}