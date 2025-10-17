<?php

namespace App\Http\Controllers\Api\React\User\Auth;


use Exception;
use App\Models\User;
// use App\Mail\SendOtpMail;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class ResetPasswordController extends Controller
{
    use ApiResponse;

    //send forget otp
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }

            $otp = rand(1000, 9999);

            $user->update([
                'otp'            => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(5),
            ]);

            // Optional: Enable mail sending
            // Mail::to($user->email)->queue(new SendForgotOtpMail($otp));

            return $this->success([
                'email' => $user->email,
                'otp'   => $otp
            ], 'Forgot password OTP sent successfully.', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], 'An error occurred. Please try again later.', 500);
        }
    }



    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->error([], 'User not found', 404);
            }

            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return $this->error([], 'OTP has expired', 400);
            }

            if ($user->otp !== $request->otp) {
                return $this->error([], 'Invalid OTP', 400);
            }

            $token = Str::random(60);

            $user->update([
                'otp'                          => null,
                'otp_expires_at'               => null,
                'reset_password_token'         => $token,
                'reset_password_token_expire_at' => Carbon::now()->addMinutes(5),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'OTP verified successfully.',
                'code'    => 200,
                'token'   => $token,
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    // set new password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->error([], 'User not found', 404);
            }

            if (
                empty($user->reset_password_token) ||
                $user->reset_password_token !== $request->token ||
                Carbon::now()->gt($user->reset_password_token_expire_at)
            ) {
                Log::error('Invalid token or token expired', [
                    'expires_at' => $user->reset_password_token_expire_at,
                ]);
                return $this->error([], 'Invalid token or token expired', 401);
            }

            $user->update([
                'password'                    => Hash::make($request->password),
                'reset_password_token'        => null,
                'reset_password_token_expire_at' => null,
            ]);

            return $this->success([], 'Password reset successfully.', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    //resend otp
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->error([], 'User not found', 404);
            }

            if ($user->otp_expires_at) {
                $lastSent = $user->otp_expires_at->subMinutes(5);
                $secondsSinceLast = (int) $lastSent->diffInSeconds(now());
                if ($secondsSinceLast < 60) {
                    $secondsLeft = 60 - $secondsSinceLast;
                    return $this->error([], 'Please wait ' . $secondsLeft . ' sec before requesting a new OTP.', 429);
                }
            }

            $otp = rand(1000, 9999);
            $otpExpiresAt = now()->addMinutes(5);

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
            ]);

            // You can send the OTP via email or SMS here. Example:
            // Mail::to($user->email)->queue(new SendOtpMail($otp));

            return $this->success(['otp' => $otp], 'OTP resent successfully.', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
