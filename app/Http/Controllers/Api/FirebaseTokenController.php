<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FirebaseTokens;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;


class FirebaseTokenController extends Controller
{
    public function test()
    {
        $user = User::find(auth('api')->user()->id);
        if ($user && $user->firebaseTokens) {
            $notifyData = ['title' => "Payment Failed", 'body'  => "test body", 'icon'  => config('settings.logo')];
            foreach ($user->firebaseTokens as $firebaseToken) {
                Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Token saved successfully',
            'data' => $user->firebaseTokens,
            'code' => 200,
        ], 200);
    }

    /**
     * News Serve For Frontend
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'device_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        //first delete existing token
        $firebase = FirebaseTokens::where('user_id', auth('api')->user()->id)->where('device_id', $request->device_id);
        if ($firebase) {
            $firebase->delete();
        }

        try {
            $data = new FirebaseTokens();
            $data->user_id = auth('api')->user()->id;
            $data->token = $request->token;
            $data->device_id = $request->device_id;
            $data->status = "active";
            $data->save();

            return response()->json([
                'status' => true,
                'message' => 'Token saved successfully',
                'data' => $data,
                'code' => 200,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'No records found',
                'code' => 418,
                'data' => [],
            ], 418);
        }
    }

    /**
     * Get Single Record
     * @param $token, $device_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
        $user_id = auth('api')->user()->id;
        $device_id = $request->device_id;
        $data = FirebaseTokens::where('user_id', $user_id)->where('device_id', $device_id)->first();
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'No records found',
                'code' => 404,
                'data' => [],
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Token fetched successfully',
            'data' => $data,
            'code' => 200,
        ], 200);
    }

    /**
     * Delete Token Single Record
     * @param $token, $device_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $user = FirebaseTokens::where('user_id', auth('api')->user()->id)->where('device_id', $request->device_id);
        if ($user) {
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'Token deleted successfully',
                'code' => 200,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No records found',
                'code' => 404,
            ], 404);
        }
    }
}
