<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    use ApiResponse;


    /**
     * get user progile
     */
    public function profile()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not found.', 200);
            }
            // $profile = User::with('friends')->where('id', $user->id)->first();

            return $this->success(new UserResource($user), 'User Profile Retrieved Successfully', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth('api')->user();

            $validator = Validator::make($request->all(), [
                'first_name' => ['nullable', 'string', 'max:50'],
                'last_name' => ['nullable', 'string', 'max:50'],
                'avatar' => ['nullable', 'image'],
                'bio' => ['nullable', 'string', 'max:100'],
                'username' => ['nullable', 'string', 'max:50', 'unique:users,username,' . $user->id],
                'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $data = $validator->validated();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Helper::deleteImage($user->avatar);
                }
                $avatarPath = Helper::uploadImage($request->file('avatar'), 'profile');
                $data['avatar'] = $avatarPath;
            }

            // Update user
            $user->update($data);

            return $this->success(new UserResource($user), 'Profile updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Profile Update Error: ' . $e->getMessage(), [
                'user_id' => auth('api')->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'Failed to update profile.', 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string', 'min:8'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            // Check if user has password (for social login users)
            if (!$user->password) {
                return $this->error([], 'You are using social login. Please set up a password first.', 400);
            }

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->error([], 'Current password is incorrect.', 401);
            }

            // Update password
            $user->update(['password' => Hash::make($request->password)]);

            // Optional: Invalidate all tokens except current one
            // auth('api')->refresh();

            return $this->success([], 'Password updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Password Update Error: ' . $e->getMessage(), [
                'user_id' => auth('api')->id(),
            ]);
            return $this->error([], 'Failed to update password.', 500);
        }
    }

    /**
     * Delete profile (soft delete)
     */
    public function deleteProfile(Request $request)
    {
        try {
            $user = auth('api')->user();

            // Validate password
            $validator = Validator::make($request->all(), [
                'password' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            // Check if user has password (for social login users)
            if (!$user->password) {
                return $this->error([], 'Cannot delete account. Please contact support.', 400);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return $this->error([], 'Password is incorrect.', 401);
            }

            // Delete images
            if ($user->avatar) {
                Helper::deleteImage($user->avatar);
            }

            if ($user->cover) {
                Helper::deleteImage($user->cover);
            }

            // Soft delete user
            $user->delete();

            // Logout and invalidate token
            auth('api')->logout();

            return $this->success([], 'Profile deleted successfully.', 200);
        } catch (Exception $e) {
            Log::error('Profile Delete Error: ' . $e->getMessage(), [
                'user_id' => auth('api')->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'Failed to delete profile.', 500);
        }
    }
}
