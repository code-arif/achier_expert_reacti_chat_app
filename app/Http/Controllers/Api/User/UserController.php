<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    use ApiResponse;

    // get user profile
    public function userDetais($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->error([], 'User not found.', 200);
            }
            return $this->success(new UserResource($user), 'User Profile Retrieved Successfully', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
