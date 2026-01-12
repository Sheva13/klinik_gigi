<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\MpUser;
use App\Models\Otp;
use App\Models\OtpAuditLog;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Exception;

class AuthController extends Controller
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
                'alamat'            => 'nullable|string|max:1000',
                'password'          => 'required|string|min:8|confirmed',
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
                    'alamat'          => $validated['alamat'] ?? null,
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
                    'alamat' => $user->alamat,
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
    public function verifyOtpEmail(Request $request)
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
    
        // Find or create user
        $user = MpUser::where('email', $email)->first();
    
        if (!$user) {
            $user = MpUser::create([
                'user_id'       => $this->generateUniqueUserId(),
                'email'         => $email,
                'nama_pengguna' => explode('@', $email)[0],
                'password'      => Hash::make(Str::random(16))
            ]);
        }
    
        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;
    
        $user->update(['current_token' => $token]);
    
        return response()->json([
            'success' => true,
            'message' => 'Verifikasi berhasil',
            'data' => [
                'user_id'         => $user->user_id,
                'nama_pengguna'   => $user->nama_pengguna,
                'email'           => $user->email,
                'token'           => $token,
                'token_expires_in'=> config('sanctum.expiration') ?? null,
            ]
        ]);
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