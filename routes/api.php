<?php

use App\Http\Controllers\Api\User\UserBlockController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Chat\ChatController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Chat\GroupChatController;
use App\Http\Controllers\Api\Friend\FriendsController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use App\Http\Controllers\Api\Friend\FindFriendController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Friend\ReportUserController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Friend\FriendRequestController;

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

    // //Notification
    // Route::get('/my-notifications', [NotificationController::class, 'allNotifications']); //get all notification
    // Route::post('/read-notification/{id}', [NotificationController::class, 'readNotification']); //mark as read single notification
    // Route::post('/read-all-notifications', [NotificationController::class, 'readAllNotifications']); //mark as read all notification

    Route::middleware(['auth:api'])->controller(ChatController::class)->prefix('auth/chat')->group(function () {
        Route::get('/list', 'listCombined'); // working
        Route::post('/send/{receiver_id}', 'send'); // working
        Route::get('/conversation/{receiver_id}', 'conversation'); // working
        Route::get('room/{receiver_id}', 'room');
        Route::get('/search', 'search'); // working
        Route::get('/seen/all/{receiver_id}', 'seenAll'); // working
        Route::get('/seen/single/{chat_id}', 'seenSingle'); // working
        Route::delete('/delete/{receiver_id}', 'deleteChat'); // working
        Route::delete('/delete/chat/messages', 'deleteMessages'); // working
    });


    // New group chat routes
    Route::middleware(['auth:api'])->controller(GroupChatController::class)->prefix('auth/group')->group(function () {
        Route::post('/create', 'createGroup'); // working
        Route::get('/list', 'listGroups'); // working
        Route::get('/{group_id}', 'groupDetails'); // working
        Route::post('/{group_id}/send', 'sendMessage'); // working
        Route::post('/{group_id}/message/{message_id}', 'editMessage'); // working
        Route::get('/{group_id}/messages', 'getMessages'); //working
        Route::post('/{group_id}/read', 'markAsRead'); // working
        Route::post('/{group_id}/add-members', 'addMembers'); // working
        Route::delete('/{group_id}/remove-member/{user_id}', 'removeMember'); // working
        Route::post('/{group_id}/make-admin/{user_id}', 'makeAdmin'); // working
        Route::post('/{group_id}/leave', 'leaveGroup'); // working
        Route::delete('/{group_id}/delete', 'deleteGroup'); // working
        Route::delete('/{group_id}/delete-messages', 'deleteMessages'); //working
        Route::post('/{group_id}/update', 'updateGroup'); // working
    });
});
