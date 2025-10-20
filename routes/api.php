<?php

use App\Http\Controllers\Api\Friend\FindFriendController;
use App\Http\Controllers\Api\Friend\FriendBlockController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Chat\ChatController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Friend\FriendRequestController;
use App\Http\Controllers\Api\Friend\FriendsController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\User\UserController;

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
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']); // working
    Route::delete('/delete-profile', [UserProfileController::class, 'deleteProfile']); // working

    // find contact
    Route::post('/find-contacts', [FindFriendController::class, 'findContacts']);


    // Friend request system
    Route::prefix('/friends')->group(function () {
        Route::post('/send-request', [FriendRequestController::class, 'sendRequest']); // working: send friend request
        Route::post('/cancel-request', [FriendRequestController::class, 'cancelRequest']); // working: cancle friend request
        Route::post('/accept-request', [FriendRequestController::class, 'acceptRequest']); // working: accept friend request
        Route::post('/decline-request', [FriendRequestController::class, 'declineRequest']); // working: decline friend request
        Route::get('/requests', [FriendRequestController::class, 'getRequests']); // working: all incoming requests


        Route::get('/list', [FriendsController::class, 'friendList']); // all firend list all auth user

        Route::get('/users/{user}/', [FriendsController::class, 'userFriendList']); // Get another user's friend list
    });

            // Get user details
        Route::get('/user-profile/{userId}', [UserController::class, 'userDetais']);

    // User Block system
    Route::prefix('/block')->group(function () {
        Route::post('/user', [FriendBlockController::class, 'blockUser']);
        Route::post('/user/unblock', [FriendBlockController::class, 'unblockUser']);
        Route::get('/list', [FriendBlockController::class, 'blockedUsers']);
    });

    //Notification
    Route::get('/my-notifications', [NotificationController::class, 'allNotifications']); //get all notification
    Route::post('/read-notification/{id}', [NotificationController::class, 'readNotification']); //mark as read single notification
    Route::post('/read-all-notifications', [NotificationController::class, 'readAllNotifications']); //mark as read all notification
});


Route::middleware(['auth:api'])->controller(ChatController::class)->prefix('auth/chat')->group(function () {
    Route::get('/list', 'list');
    Route::post('/send/{receiver_id}', 'send');
    Route::get('/conversation/{receiver_id}', 'conversation');
    Route::get('room/{receiver_id}', 'room');
    Route::get('/search', 'search');
    Route::get('/seen/all/{receiver_id}', 'seedAll');
    Route::get('/seen/single/{chat_id}', 'seenSingle');
    Route::delete('/delete/{receiver_id}', 'deleteChat');
});
