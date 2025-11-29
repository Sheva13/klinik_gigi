<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\MpUser;
use App\Models\Otp;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Exception;

class UbahPasswordController extends Controller
{
    private $otpExpireMinutes = 5;

    /**
     * Kirim OTP untuk ganti password
     */
    public function requestOtpForPasswordChange(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        if (!$user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak memiliki email. Tidak dapat mengirim OTP.'
            ], 400);
        }

        $email = strtolower($user->email);

        // Cooldown 60 detik
        $cooldownKey = "otp_pwd:cooldown:" . md5($email);
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Tunggu sebentar sebelum meminta OTP lagi.'
            ], 429);
        }

        // Generate OTP 6 digit
        $pin = random_int(100000, 999999);
        $hashed = Hash::make((string) $pin);

        $expiresAt = Carbon::now()->addMinutes($this->otpExpireMinutes);

        // Simpan OTP hanya untuk change password
        Otp::create([
            'email'     => $email,
            'code_hash' => $hashed,
            'expires_at'=> $expiresAt,
            'purpose'   => 'CHANGE_PASSWORD',
        ]);

        // Kirim email
        try {
            Mail::to($email)->queue(new OtpMail($pin, $this->otpExpireMinutes));
        } catch (Exception $e) {
            Log::error("OTP Send Error: " . $e->getMessage());
        }

        Cache::put($cooldownKey, true, 60);

        return response()->json([
            'success' => true,
            'message' => 'OTP untuk ubah password telah dikirim.'
        ]);
    }

    /**
     * Validasi OTP + Ubah Password
     */
    public function verifyOtpAndChangePassword(Request $request)
    {
        $request->validate([
            'otp'            => 'required|string',
            'password_lama'  => 'required|string',
            'password_baru'  => 'required|string|min:8|confirmed'
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $email = strtolower($user->email);

        // Ambil OTP valid terbaru
        $otp = Otp::where('email', $email)
            ->whereNull('used_at')
            ->where('purpose', 'CHANGE_PASSWORD')
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('id', 'desc')
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau sudah kadaluarsa.'
            ], 404);
        }

        // Cek OTP
        if (!Hash::check($request->otp, $otp->code_hash)) {
            $otp->increment('attempts');

            if ($otp->attempts >= 5) {
                $otp->update(['used_at' => Carbon::now()]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah.'
            ], 401);
        }

        // Cek password lama
        if (!Hash::check($request->password_lama, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.'
            ], 400);
        }

        // ----- Ganti password -----
        $user->update([
            'password' => Hash::make($request->password_baru)
        ]);

        // Tandai OTP sudah dipakai
        $otp->update(['used_at' => Carbon::now()]);

        // Hapus semua token lama (logout semua device)
        $user->tokens()->delete();
        $user->update(['current_token' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login ulang.'
        ]);
    }
}
