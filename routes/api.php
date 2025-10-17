<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\React\Chat\ChatController;
use App\Http\Controllers\Api\React\DashboardController;
use App\Http\Controllers\Api\React\User\FollowerController;
use App\Http\Controllers\Api\React\User\Auth\SocialLoginController;
use App\Http\Controllers\Api\React\User\Auth\UserProfileController;
use App\Http\Controllers\Api\React\User\Auth\ResetPasswordController;
use App\Http\Controllers\Api\React\User\Auth\AuthenticationController;
use App\Http\Controllers\Api\React\Notification\NotificationController;

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

    //user followers and friends routes
    Route::post('/follow/{id}', [FollowerController::class, 'toggleFollow']); // Follow or unfollow a user by ID (toggle)
    Route::get('/followers', [FollowerController::class, 'getFollowers']); // Get all followers of authenticated user
    Route::get('/user/{id}/followers', [FollowerController::class, 'getUserFollowers']); // Get all followers of a user by user ID
    Route::get('/followings', [FollowerController::class, 'getFollowings']); // Get all users that a user is following who is authenticated
    Route::get('/user/{id}/followings', [FollowerController::class, 'getUserFollowings']); // Get all users that a user is following by user ID
    Route::get('/friends', [FollowerController::class, 'getFriends']); // Get auth user friend list

    //Notification
    Route::get('/my-notifications', [NotificationController::class, 'allNotifications']); //get all notification
    Route::post('/read-notification/{id}', [NotificationController::class, 'readNotification']); //mark as read single notification
    Route::post('/read-all-notifications', [NotificationController::class, 'readAllNotifications']); //mark as read all notification

    //Dashboard routes
    Route::get('/user-event-stats', [DashboardController::class, 'userEventStats']); // user event stats
    Route::get('/venue-rating-stats', [DashboardController::class, 'venueReviewStats']); // venue rating stats
    // Route::get('/event-duration-stats', [DashboardController::class,'eventDurationStats']); // event duration stats

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

