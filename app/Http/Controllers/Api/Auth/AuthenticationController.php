<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Mail\EmailVerifyMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest as ApiLoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\UserRegisterRequest;
use App\Helper\Helper;

class AuthenticationController extends Controller
{
    use ApiResponse;

    /**
     * Register (store temporary data in cache)
     */
    public function register(UserRegisterRequest $request)
    {
        try {
            $email = strtolower(trim($request->email));

            // Rate limiting
            if (Cache::has("register_otp_{$email}")) {
                $remainingTime = Cache::get("register_otp_time_{$email}") - now()->timestamp;
                if ($remainingTime > 0) {
                    return $this->error([], "Please wait {$remainingTime} seconds before requesting a new OTP.", 429);
                }
            }

            // Generate unique username
            $username = Helper::generateUniqueUsername($request->first_name, $request->last_name);

            // Generate OTP
            $otp = random_int(1000, 9999);
            $otpExpiresAt = now()->addMinutes(5);

            // Cache data (including username)
            $cacheData = [
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $email,
                'phone'      => $request->phone,
                'password'   => Hash::make($request->password),
                'username'   => $username, // Add username
                'otp'        => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'attempts'   => 0,
            ];

            Cache::put("register_otp_{$email}", $otp, 300);
            Cache::put("register_data_{$email}", $cacheData, 300);
            Cache::put("register_otp_time_{$email}", now()->addMinutes(2)->timestamp, 120);

            // Send OTP
            Mail::to($email)->send(new EmailVerifyMail(
                $otp,
                $request->first_name ?? 'User',
                'Verify Your Email Address'
            ));

            return $this->success([
                'email' => $email,
                'expires_in' => '5 minutes',
                'username' => $username, // Optional: show in response
                'otp' => $otp . ' (Testing only)',
            ], 'OTP sent successfully. Please check your email.');
        } catch (Exception $e) {
            Log::error('Registration OTP Error: ' . $e->getMessage());
            return $this->error([], 'Failed to send verification code.', 500);
        }
    }

    /**
     * Resend OTP (update cache)
     */
    public function resendRegisterOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|max:191',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {
            $email = $request->email;
            $cachedData = Cache::get("register_data_{$email}");

            if (!$cachedData) {
                return $this->error([], 'No pending registration found. Please register again.', 404);
            }

            // New OTP
            $otp = rand(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(5);

            // Update cache
            $cachedData['otp'] = $otp;
            $cachedData['otp_expires_at'] = $otpExpiresAt;

            Cache::put("register_otp_{$email}", $otp, 300);
            Cache::put("register_data_{$email}", $cachedData, 300);

            // Resend OTP to email
            Mail::to($email)->send(new EmailVerifyMail($otp, $request->first_name ?? 'User', 'Verify Your Email Address'));

            return $this->success([
                'email' => $email,
                'otp' => $otp,
            ], 'OTP resent successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Verify OTP and create user
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {
            $email = $request->email;
            $cachedOtp = Cache::get("register_otp_{$email}");
            $cachedData = Cache::get("register_data_{$email}");

            if (!$cachedData || !$cachedOtp) {
                return $this->error([], 'No registration data found or OTP expired. Please register again.', 404);
            }

            if ($cachedOtp != $request->otp) {
                return $this->error([], 'Invalid OTP. Please try again.', 403);
            }

            if (Carbon::parse($cachedData['otp_expires_at'])->isPast()) {
                return $this->error([], 'OTP has expired. Please request a new one.', 403);
            }

            // Create user - password is already hashed in cache
            $user = User::create([
                'first_name' => $cachedData['first_name'],
                'last_name' => $cachedData['last_name'],
                'username'   => $cachedData['username'],
                'email' => $cachedData['email'],
                'phone' => $cachedData['phone'],
                'password' => $cachedData['password'],
                'otp_verified_at' => now(),
                'status' => 'active',
            ]);

            // Clean cache
            Cache::forget("register_otp_{$email}");
            Cache::forget("register_data_{$email}");
            Cache::forget("register_otp_time_{$email}");  // Don't forget to clean this too

            // Generate token
            $token = JWTAuth::fromUser($user);

            $user->token = $token;
            $user->is_new_user = empty($user->first_name) || empty($user->last_name);

            return $this->success($user, 'Email verification successful.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }


    /*
    ** User login
    */
    public function login(ApiLoginRequest $request)
    {
        try {
            $validated = $request->validated();
            $email = trim(strtolower($validated['email']));
            $password = $validated['password'];

            // Find active user (not soft-deleted)
            $user = User::where('email', $email)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->first();

            if (!$user) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            // Check OTP verification
            if (!$user->otp_verified_at) {
                return $this->error([], 'Please verify your email before logging in.', 401);
            }

            // Attempt login
            if (!auth('api')->attempt(['email' => $email, 'password' => $password])) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            // Update last activity
            $user->update(['last_activity_at' => now()]);

            $user->makeHidden(['password', 'otp', 'reset_password_token']);

            $data = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'token' => auth('api')->tokenById($user->id),
                'last_activity_at' => $user->last_activity_at,
            ];

            return $this->success($data, 'Successfully logged in!', 200);
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return $this->error([], 'Something went wrong. Please try again later.', 500);
        }
    }



    /*
    ** User logout
    */
    public function logout()
    {
        try {
            auth('api')->logout();
            return $this->success([], 'Successfully logged out.', 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
