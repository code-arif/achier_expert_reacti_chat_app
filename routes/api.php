<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\PrivacyController;
use App\Http\Controllers\Api\Chat\ChatController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\FirebaseTokenController;
use App\Http\Controllers\Api\Friend\FriendsController;
use App\Http\Controllers\Api\User\UserBlockController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use App\Http\Controllers\Api\Friend\FindFriendController;
use App\Http\Controllers\Api\Friend\ReportUserController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Chat\V2\SingleChatController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Friend\FriendRequestController;
use App\Http\Controllers\Api\Chat\Group\GroupCreateController;
use App\Http\Controllers\Api\Chat\Group\GroupMessageController;
use App\Http\Controllers\Api\Chat\Group\GroupManageMemberController;

//health-check
Route::get("/check", function () {
    return "Project is running!";
});

//Guest user routes
Route::group(['middleware' => 'guest:api'], function () {
    Route::post('/login', [AuthenticationController::class, 'login']); // working
    Route::post('/register', [AuthenticationController::class, 'register']); // wroking
    Route::post('/resend-register-otp', [AuthenticationController::class, 'resendRegisterOtp']); // working
    Route::post('/email-verify', [AuthenticationController::class, 'verifyEmail']); // working

    // Password Reset
    Route::post('/forgot-password', [ResetPasswordController::class, 'forgotPassword']); // working
    Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOTP']); // working
    Route::post('/resend-otp', [ResetPasswordController::class, 'resendOtp']); // working
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']); // working

    Route::post('social/signin/{provider}', [SocialLoginController::class, 'socialSignin']);
});



Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']); // working

    //Profile
    Route::get('/profile', [UserProfileController::class, 'profile']); // working
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']); // working
    Route::post('/update-username', [UserProfileController::class, 'updateUsername']); // working
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']); // working
    Route::delete('/delete-profile', [UserProfileController::class, 'deleteProfile']); // working

    // find contact
    Route::post('/find-contacts', [FindFriendController::class, 'findContacts']);

    // user list
    Route::get('/user-list', [UserController::class, 'userList']);


    // Friend request system
    Route::prefix('/friends')->group(function () {
        Route::post('/send-request', [FriendRequestController::class, 'sendRequest']); // working: send friend request
        Route::post('/cancel-request', [FriendRequestController::class, 'cancelRequest']); // working: cancle friend request
        Route::post('/accept-request', [FriendRequestController::class, 'acceptRequest']); // working: accept friend request
        Route::post('/decline-request', [FriendRequestController::class, 'declineRequest']); // working: decline friend request
        Route::get('/requests', [FriendRequestController::class, 'getRequests']); // working: all incoming requests
        Route::get('/requests/sent/list', [FriendRequestController::class, 'getSentRequests']); // working all send friend request


        Route::get('/list', [FriendsController::class, 'friendList']); // all firend list all auth user

        Route::get('/users/{user}/', [FriendsController::class, 'userFriendList']); // Get another user's friend list

        Route::delete('/unfriend/{id}', [FriendsController::class, 'unfriend']);
    });

    // Get user details
    Route::get('/user-profile/{userId}', [UserController::class, 'userDetais']);

    // User Report system
    Route::prefix('/report')->group(function () {
        Route::post('/user/{reported_user_id}', [ReportUserController::class, 'reportUser']); // working
        Route::get('/list', [ReportUserController::class, 'reportedUsers']); // working
    });

    // User Block system
    Route::prefix('/block')->group(function () {
        Route::post('/user/{block_user_id}', [UserBlockController::class, 'toggleBlock']); // working
        Route::get('/list', [UserBlockController::class, 'blockedUsers']); // working
    });

    Route::middleware(['auth:api'])->controller(ChatController::class)->prefix('auth/chat')->group(function () {
        Route::get('/list', 'listCombined'); // working
        Route::post('/send/{receiver_id}', 'send'); // working
        Route::get('/conversation/{receiver_id}', 'conversation'); // working
        Route::get('room/{receiver_id}', 'room');
        Route::get('/search', 'search'); // working
        Route::get('/seen/all/{receiver_id}', 'seenAll'); // working
        Route::get('/seen/single/{chat_id}', 'seenSingle'); // working
        Route::delete('/delete/{receiver_id}', 'deleteChat'); // working
        Route::delete('/delete/chat/messages', 'deleteMessage'); // working
        Route::post('/mark-viewed/{message_id}', 'markAsViewed'); // wroking
    });

    /*
    |--------------------------------------------------------------------------
    | Chatting System Version 2.0 Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:api'])->prefix('v2/auth/chat')->group(function () {
        // Chat list
        Route::get('/list', [SingleChatController::class, 'listCombined']); // Combined user + group chat list

        // Send message
        Route::post('/send/{receiver_id}', [SingleChatController::class, 'send']);

        // Get conversation
        Route::get('/conversation/{receiver_id}', [SingleChatController::class, 'conversation']);

        // Get/create room
        Route::get('/room/{receiver_id}', [SingleChatController::class, 'room']);

        // Search users
        Route::get('/search', [SingleChatController::class, 'search']);

        // Mark messages as read/seen
        Route::get('/seen/all/{receiver_id}', [SingleChatController::class, 'seenAll']); // Mark all as read
        Route::get('/seen/single/{chat_id}', [SingleChatController::class, 'seenSingle']); // Mark single as read

        // Mark media as viewed (unblur)
        Route::post('/mark-viewed/{message_id}', [SingleChatController::class, 'markAsViewed']);

        // Delete operations
        Route::delete('/delete/{receiver_id}', [SingleChatController::class, 'deleteChat']); // Delete entire conversation
        Route::delete('/delete/chat/messages', [SingleChatController::class, 'deleteMessage']); // Delete single message

        // NEW FEATURES
        Route::post('/forward', [SingleChatController::class, 'forwardMessage']); // Forward message
        Route::post('/typing/{receiver_id}', [SingleChatController::class, 'typingStatus']); // Update typing status
    });


    // New group chat routes
    Route::middleware(['auth:api'])->prefix('auth/group')->group(function () {

        // gorup opertation routes
        Route::post('/create', [GroupCreateController::class, 'createGroup']); // working
        Route::get('/list', [GroupCreateController::class, 'listGroups']); // done
        Route::get('/{group_id}', [GroupCreateController::class, 'groupDetails']); // done
        Route::post('/{group_id}/update', [GroupCreateController::class, 'updateGroup']); // absolutely working
        Route::post('/{group_id}/update/avatar', [GroupCreateController::class, 'updateAvatar']); // working

        // message routes
        Route::post('/{group_id}/send', [GroupMessageController::class, 'sendMessage']); // working
        Route::post('/{group_id}/message/{message_id}', [GroupMessageController::class, 'editMessage']); // working
        Route::get('/{group_id}/messages', [GroupMessageController::class, 'getMessages']); //working
        Route::post('/mark-viewed/{message_id}', [GroupMessageController::class, 'markAsViewed']); // wroking
        Route::get('/{group_id}/messages/media', [GroupMessageController::class, 'messageMedia']);
        Route::post('/{group_id}/read', [GroupMessageController::class, 'markAsRead']); // working
        Route::delete('/{group_id}/delete-messages', [GroupMessageController::class, 'deleteMessages']); //working

        // group member routes
        Route::post('/{group_id}/add-members', [GroupManageMemberController::class, 'addMembers']); // working
        Route::delete('/{group_id}/remove-member/{user_id}', [GroupManageMemberController::class, 'removeMember']); // working
        Route::post('/{group_id}/make-admin/{user_id}', [GroupManageMemberController::class, 'makeAdmin']); // working
        Route::post('/{group_id}/remove-admin/{user_id}', [GroupManageMemberController::class, 'removeAdmin']); // working
        Route::post('/{group_id}/leave', [GroupManageMemberController::class, 'leaveGroup']); // working
        Route::delete('/{group_id}/delete', [GroupManageMemberController::class, 'deleteGroup']); // working
        Route::get('/{group}/available-users', [GroupManageMemberController::class, 'availableUsers']);
    });

    // Privacy Policy and Terms & Conditions
    Route::get('/privacy-policy', [PrivacyController::class, 'index']); //


    Route::middleware(['auth:api'])->controller(FirebaseTokenController::class)->prefix('firebase')->group(function () {
        Route::get("test", "test");
        Route::post("token/add", "store");
        Route::post("token/get", "getToken");
        Route::post("token/delete", "deleteToken");
    });


    // Test S3 Connection
    Route::get('/test-s3', function () {
        try {
            Storage::disk('s3')->put('test.txt', 'Hello S3!');
            $url = Storage::disk('s3')->url('test.txt');
            Storage::disk('s3')->delete('test.txt');

            return response()->json([
                'success' => true,
                'message' => 'S3 connection successful!',
                'test_url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    });
});
