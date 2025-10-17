<?php


namespace App\Http\Controllers\Api\React\User\Auth;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    use ApiResponse;

    protected $client_id;
    protected $key_id;
    protected $team_id;
    protected $private_key;
    protected $redirect_url;

   public function __construct()
    {
        $this->client_id = config('services.apple.client_id');
        $this->key_id = config('services.apple.key_id');
        $this->team_id = config('services.apple.team_id');
        $this->private_key = config('services.apple.private_key');
        $this->redirect_url = config('services.apple.redirect');

        // dd($this->client_id, $this->key_id, $this->team_id, $this->private_key, $this->redirect_url);

    }





    public function googleAuthentication(Request $request)
    {

       try {

            $token = $request->input('token');

            $googleUser = Socialite::driver('google')->stateless()->userFromToken($token);


            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt($googleUser->getId()),
                    'is_otp_verified' => 1,
                    'is_google_signin' => true,
                ]
            );

            Auth::login($user);

            $token = auth('api')->login($user);

            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'token' => $token,
            ];

            return $this->success($userData, 'Successfully Logged In With Google', 200);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 'Google Sign In Failed', 500);
        }
    }



}

