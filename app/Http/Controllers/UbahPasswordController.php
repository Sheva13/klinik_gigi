<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Otp;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Exception;

class UbahPasswordController extends Controller
{
    private $otpExpireMinutes = 5;

    /**
     * 1. Kirim OTP
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
                'message' => 'Akun tidak memiliki email.'
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

        $pin = random_int(100000, 999999);
        $hashed = Hash::make((string) $pin);

        $expiresAt = Carbon::now()->addMinutes($this->otpExpireMinutes);

        Otp::create([
            'email'     => $email,
            'code_hash' => $hashed,
            'expires_at'=> $expiresAt,
            'purpose'   => 'CHANGE_PASSWORD',
        ]);

        try {
            Mail::to($email)->queue(new OtpMail($pin, $this->otpExpireMinutes));
        } catch (Exception $e) {
            Log::error("OTP Send Error: " . $e->getMessage());
        }

        Cache::put($cooldownKey, true, 60);

        return response()->json([
            'success' => true,
            'message' => 'OTP telah dikirim.'
        ]);
    }


    /**
     * 2. Verifikasi OTP saja (page 1)
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $email = strtolower($user->email);

        $otp = Otp::where('email', $email)
            ->whereNull('used_at')
            ->where('purpose', 'CHANGE_PASSWORD')
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('id', 'desc')
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau kadaluarsa.'
            ], 404);
        }

        if (!Hash::check($request->otp, $otp->code_hash)) {
            $otp->increment('attempts');

            if ($otp->attempts >= 5) {
                $otp->update(['used_at' => Carbon::now()]);
            }

            return response()->json([
                'success' => false,
                'message' => 'OTP salah.'
            ], 401);
        }

        // Tandai OTP valid → sehingga user bisa lanjut ke page 2
        $otp->update(['used_at' => Carbon::now()]);

        return response()->json([
            'success' => true,
            'message' => 'OTP valid. Silakan lanjut ke reset password.'
        ]);
    }


    /**
     * 3. Reset password (page 2)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password_baru' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $user->update([
            'password' => Hash::make($request->password_baru)
        ]);

        $user->tokens()->delete();
        $user->update(['current_token' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login ulang.'
        ]);
    }
}
