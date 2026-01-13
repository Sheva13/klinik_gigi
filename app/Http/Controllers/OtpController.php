<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\MpUser;
use App\Models\Otp;
use App\Models\OtpAuditLog;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Exception;

class OtpController extends Controller
{
    // Configurable via env
    private $otpExpireMinutes;
    private $maxRequestsPerHour;
    private $resendCooldownSeconds;
    private $maxVerifyAttemptsPerOtp;
    private $blockAfterFailedAttempts;
    private $blockMinutes;

    public function __construct()
    {
        $this->otpExpireMinutes = env('OTP_EXPIRE_MINUTES', 5);
        $this->maxRequestsPerHour = env('OTP_MAX_REQUESTS_PER_HOUR', 3);
        $this->resendCooldownSeconds = env('OTP_RESEND_COOLDOWN_SECONDS', 60);
        $this->maxVerifyAttemptsPerOtp = env('OTP_MAX_VERIFY_ATTEMPTS', 5);
        $this->blockAfterFailedAttempts = env('OTP_BLOCK_AFTER_FAILED_ATTEMPTS', 10);
        $this->blockMinutes = env('OTP_BLOCK_MINUTES', 15);
    }

    private function generateUniqueUserId()
    {
        $tanggal = Carbon::now()->format('Ymd');
        do {
            $random = random_int(100, 999);
            $userId = "PSN{$tanggal}{$random}";
        } while (MpUser::where('user_id', $userId)->exists());

        return $userId;
    }

    // Generate No RM Unik: RM + YYMMDD + 3 Random Angka
    private function generateNoRM()
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

    /**
     * Request OTP via email
     */
    public function requestOtpEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = strtolower($request->input('email'));

        // Check if blocked
        if ($this->isBlocked($email)) {
            $this->audit($email, 'blocked', ['reason' => 'blocked_temporary']);
            return response()->json(['success' => false, 'message' => 'Too many attempts. Try again later.'], 429);
        }

        // Rate limit: max requests per hour
        if (!$this->allowRequestOtp($email)) {
            $this->audit($email, 'rate_limited', ['reason' => 'requests_per_hour_exceeded']);
            return response()->json(['success' => false, 'message' => 'Exceeded OTP request limit. Try later.'], 429);
        }

        // Resend cooldown
        $cooldownKey = $this->cooldownKey($email);
        if (Cache::has($cooldownKey)) {
            return response()->json(['success' => false, 'message' => 'Please wait before requesting another code.'], 429);
        }

        // Generate numeric OTP (6 digits)
        $pin = random_int(100000, 999999);
        $hashed = Hash::make((string)$pin);

        $expiresAt = Carbon::now()->addMinutes($this->otpExpireMinutes);

        // Save to DB
        $otp = Otp::create([
            'email' => $email,
            'code_hash' => $hashed,
            'expires_at' => $expiresAt,
            'request_ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Queue email
        try {
            Mail::to($email)->queue(new OtpMail($pin, $this->otpExpireMinutes));
            $sendResult = 'queued';
        } catch (Exception $e) {
            // Log, but still audit
            Log::error('OTP Mail send error: ' . $e->getMessage());
            $sendResult = 'failed: ' . $e->getMessage();
        }

        // Audit log
        $this->audit($email, 'request_sent', [
            'otp_id' => $otp->id,
            'expires_at' => $expiresAt->toDateTimeString(),
            'send' => $sendResult,
            'ip' => $request->ip(),
        ]);

        // Update request counter (rate limit) and set resend cooldown
        $this->incrementRequestCounter($email);
        Cache::put($cooldownKey, true, $this->resendCooldownSeconds);

        return response()->json(['success' => true, 'message' => 'OTP sent if the email exists.']);
    }

    /**
     * Verify OTP
     * Body: { email, code }
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string'
        ]);

        $email = strtolower($request->email);
        $code  = $request->code;

        // Check block
        if ($this->isBlocked($email)) {
            $this->audit($email, 'blocked', [
                'reason' => 'blocked_temporary_on_verify'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Try again later.'
            ], 429);
        }

        // Ambil OTP terbaru yang masih aktif
        $otp = Otp::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otp) {
            $this->incrementFailedAttempt($email);
            $this->audit($email, 'verification_failed', [
                'reason' => 'no_active_otp',
                'ip'     => $request->ip()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak ditemukan atau sudah kadaluarsa.'
            ], 404);
        }

        // Attempt limit
        if ($otp->attempts >= $this->maxVerifyAttemptsPerOtp) {
            $otp->markUsed(); // Hanguskan OTP yg disalahgunakan
            $this->incrementFailedAttempt($email);
            $this->audit($email, 'verification_failed', [
                'reason' => 'attempts_exceeded',
                'otp_id' => $otp->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Maksimal percobaan verifikasi tercapai.'
            ], 429);
        }

        // CHECK HASH
        $isValid = Hash::check($code, $otp->code_hash);

        if (!$isValid) {
            // hanya tambah attempt kalau salah
            $otp->increment('attempts');

            $this->incrementFailedAttempt($email);
            $this->audit($email, 'verification_failed', [
                'reason'   => 'invalid_code',
                'otp_id'   => $otp->id,
                'attempts' => $otp->attempts
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Kode salah.'
            ], 401);
        }

        // ----- IF VALID -----
        $otp->markUsed();
        $this->audit($email, 'verification_success', [
            'otp_id' => $otp->id,
            'ip'     => $request->ip()
        ]);

        // Clear fail counters
        $this->clearFailedAttempts($email);

        // Mulai transaksi untuk membuat rekam_medis dan mp_users
        DB::beginTransaction();
        try {
            // Cari data registrasi sementara
            $pendingRegistration = DB::table('pending_registrations')
                ->where('email', $email)
                ->first();

            if (!$pendingRegistration) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data registrasi tidak ditemukan. Silakan daftar ulang.'
                ], 404);
            }

            $user = null;
            $finalNoRM = null;

            if ($pendingRegistration->tipe_pasien === 'lama') {
                $rekamMedis = DB::table('rekam_medis')
                    ->where('rekam_medis', $pendingRegistration->rekam_medis_id)
                    ->first();

                if (!$rekamMedis) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Nomor rekam medis tidak ditemukan'], 404);
                }

                $existingUser = MpUser::where('rekam_medis_id', $rekamMedis->id)->first();
                if ($existingUser) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Akun untuk rekam medis ini sudah terdaftar'], 409);
                }

                $user = MpUser::create([
                    'user_id'         => $this->generateUniqueUserId(),
                    'nama_pengguna'   => $rekamMedis->nama,
                    'nik'             => $rekamMedis->no_identitas,
                    'rekam_medis_id'  => $rekamMedis->id,
                    'tanggal_lahir'   => $rekamMedis->tanggal_lahir,
                    'jenis_kelamin'   => $rekamMedis->jenis_kelamin,
                    'no_hp'           => $rekamMedis->hp ?: ($pendingRegistration->no_hp ?? null),
                    'email'           => $pendingRegistration->email,
                    'password'        => $pendingRegistration->password,
                    'alamat'          => $rekamMedis->alamat,
                ]);

                $finalNoRM = $rekamMedis->rekam_medis;

            } else {
                // Logic Pasien Baru: Generate RM -> Insert RM -> Insert User
                $newNoRM = $this->generateNoRM();

                $rekamMedisId = DB::table('rekam_medis')->insertGetId([
                    'rekam_medis'    => $newNoRM,
                    'nama'           => $pendingRegistration->nama_pengguna,
                    'no_identitas'   => $pendingRegistration->nik,
                    'hp'             => $pendingRegistration->no_hp,
                    'alamat'         => $pendingRegistration->alamat ?? '-',
                    'tanggal_lahir'  => $pendingRegistration->tanggal_lahir,
                    'jenis_kelamin'  => $pendingRegistration->jenis_kelamin ?? null,
                    'verifikasi'     => 0, // Default belum verifikasi admin
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                $user = MpUser::create([
                    'user_id'         => $this->generateUniqueUserId(),
                    'nama_pengguna'   => $pendingRegistration->nama_pengguna,
                    'nik'             => $pendingRegistration->nik,
                    'rekam_medis_id'  => $rekamMedisId,
                    'tanggal_lahir'   => $pendingRegistration->tanggal_lahir ?? null,
                    'jenis_kelamin'   => $pendingRegistration->jenis_kelamin ?? null,
                    'no_hp'           => $pendingRegistration->no_hp,
                    'email'           => $pendingRegistration->email,
                    'alamat'          => $pendingRegistration->alamat ?? null,
                    'password'        => $pendingRegistration->password,
                ]);

                $finalNoRM = $newNoRM;
            }

            // Hapus data dari pending_registrations
            $deletedRows = DB::table('pending_registrations')
                ->where('email', $email)
                ->delete();

            // Jika tidak ada baris yang dihapus, mungkin data telah dihapus oleh permintaan lain
            if ($deletedRows === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data registrasi telah diproses oleh permintaan lain. Silakan coba kembali.'
                ], 409);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->update(['current_token' => $token]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $pendingRegistration->tipe_pasien === 'lama'
                    ? 'Akun pasien lama berhasil dibuat'
                    : 'Registrasi pasien baru berhasil',
                'data' => [
                    'user_id' => $user->user_id,
                    'nama_pengguna' => $user->nama_pengguna,
                    'no_rm' => $finalNoRM,
                    'email' => $user->email,
                    'alamat' => $user->alamat,
                    'token' => $token,
                ],
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Verification Transaction Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server saat memproses verifikasi', 'error' => $e->getMessage()], 500);
        }
    }

    /* ------------------
       Helper functions: rate limit, block, audit, counters
       ------------------ */

    private function cooldownKey($email)
    {
        return "otp:cooldown:" . md5($email);
    }

    private function requestCounterKey($email)
    {
        return "otp:requests:hour:" . date('YmdH') . ":" . md5($email);
    }

    private function failedCounterKey($email)
    {
        return "otp:failed:" . md5($email);
    }

    private function blockedKey($email)
    {
        return "otp:blocked:" . md5($email);
    }

    private function allowRequestOtp($email)
    {
        $key = $this->requestCounterKey($email);
        return Cache::get($key, 0) < $this->maxRequestsPerHour;
    }

    private function incrementRequestCounter($email)
    {
        $key = $this->requestCounterKey($email);
        Cache::increment($key);

        if (!Cache::has($key . ':ttl')) {
            Cache::put($key, Cache::get($key, 0), 3600);
            Cache::put($key . ':ttl', true, 3600);
        }
    }

    private function incrementFailedAttempt($email)
    {
        $key = $this->failedCounterKey($email);
        $count = Cache::increment($key);

        if (!$count) {
            Cache::put($key, 1, 3600);
            $count = 1;
        }

        if ($count >= $this->blockAfterFailedAttempts) {
            Cache::put($this->blockedKey($email), true, $this->blockMinutes * 60);
            $this->audit($email, 'blocked', [
                'reason'       => 'auto_block',
                'failed_count' => $count
            ]);
        }
    }

    private function clearFailedAttempts($email)
    {
        Cache::forget($this->failedCounterKey($email));
        Cache::forget($this->blockedKey($email));
    }

    private function isBlocked($email)
    {
        return Cache::has($this->blockedKey($email));
    }

    private function audit($email, $action, $meta = [])
    {
        try {
            OtpAuditLog::create([
                'email'  => $email,
                'action' => $action,
                'meta'   => $meta,
            ]);
        } catch (\Exception $e) {
            Log::error("Audit log failed: " . $e->getMessage());
        }
    }
}