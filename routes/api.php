<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\React\CMS\HomeController;
use App\Http\Controllers\Api\React\Chat\ChatController;
use App\Http\Controllers\Api\React\CMS\EventController;
use App\Http\Controllers\Api\React\DashboardController;
use App\Http\Controllers\Api\React\Post\PostController;
use App\Http\Controllers\Api\React\Venue\VenueController;
use App\Http\Controllers\Api\React\Post\PostLikeController;
use App\Http\Controllers\Api\React\User\FollowerController;
use App\Http\Controllers\Web\Backend\CMS\FeatureController;
use App\Http\Controllers\Api\React\CMS\NewsletterController;
use App\Http\Controllers\Api\React\Event\EventLikeController;
use App\Http\Controllers\Api\React\Event\EventPageController;
use App\Http\Controllers\Api\React\Venue\VenuePageController;
use App\Http\Controllers\Api\React\Post\PostCommentController;
use App\Http\Controllers\Api\React\Calendar\CalendarController;
use App\Http\Controllers\Api\React\Event\EventFilterController;
use App\Http\Controllers\Api\React\Event\EventManageController;
use App\Http\Controllers\Api\React\Venue\VenueReviewController;
use App\Http\Controllers\Api\React\Event\EventCommentController;
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
    Route::post('/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOTP']);
    Route::post('/resend-otp', [ResetPasswordController::class, 'resendOtp']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);

    Route::post('social/signin/{provider}', [SocialLoginController::class, 'socialSignin']);
});



Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']); // working

    //Profile
    Route::get('/profile', [UserProfileController::class, 'profile']); // working
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']); // working
    Route::delete('/delete-profile', [UserProfileController::class, 'deleteProfile']); // working

    Route::get('/gallery', [UserProfileController::class, 'gallery']);

    //user followers and friends routes
    Route::post('/follow/{id}', [FollowerController::class, 'toggleFollow']); // Follow or unfollow a user by ID (toggle)
    Route::get('/followers', [FollowerController::class, 'getFollowers']); // Get all followers of authenticated user
    Route::get('/user/{id}/followers', [FollowerController::class, 'getUserFollowers']); // Get all followers of a user by user ID
    Route::get('/followings', [FollowerController::class, 'getFollowings']); // Get all users that a user is following who is authenticated
    Route::get('/user/{id}/followings', [FollowerController::class, 'getUserFollowings']); // Get all users that a user is following by user ID
    Route::get('/friends', [FollowerController::class, 'getFriends']); // Get auth user friend list


    //Post routes
    Route::group(['prefix' => 'post'], function () {
        Route::post('/store', [PostController::class, 'store']); // Create new post
        Route::get('/index', [PostController::class, 'index']);  // Fetch all posts of auth user
        Route::get('/all', [PostController::class, 'getAllPosts']); // Fetch all posts of other users
        Route::get('/show/{id}', [PostController::class, 'show']); // Single post view
        Route::delete('/delete/{id}', [PostController::class, 'destroy']); // Delete post
        Route::get('/by-tag', [PostController::class, 'postsByTag']); // Get posts by hashtag
        Route::post('/update/{id}', [PostController::class, 'update']); // Update post

        //post list/unlike
        Route::group(['prefix' => 'like'], function () {
            Route::post('/{post}', [PostLikeController::class, 'toggleLike']); // Like/Unlike a post
            Route::get('/index/{postId}', [PostLikeController::class, 'index']); // Get all likes
        });

        // Comment-reply routes
        Route::group(['prefix' => 'comment'], function () {
            Route::post('/store/{postId}', [PostCommentController::class, 'store']); // Add comment
            Route::get('/list/{postId}', [PostCommentController::class, 'index']); // Get all comments
            Route::post('/update/{id}', [PostCommentController::class, 'update']); // Edit comment
            Route::delete('/delete/{id}', [PostCommentController::class, 'destroy']);  // Delete comment

            // Reply-specific routes
            Route::post('/update-reply/{replyId}', [PostCommentController::class, 'updateReply']); // Edit reply
            Route::delete('/delete-reply/{replyId}', [PostCommentController::class, 'deleteReply']); // Delete reply
        });
    });

    //Venue routes
    Route::prefix('venue')->group(function () {
        //venue page route according to prototype
        Route::get('/', [VenuePageController::class, 'allVenue']); // List all venues
        Route::get('/list', [VenuePageController::class, 'list']); // List all venues for flutter
        Route::get('/details/{id}', [VenuePageController::class, 'venueDetails']); // get a single venue with detials by id


        //venue review/feedback
        Route::prefix('reviews')->group(function () {
            Route::get('/{venue_id}', [VenueReviewController::class, 'index']); // Get all reviews for a venue
            Route::post('/{venue_id}', [VenueReviewController::class, 'store']); // Add or update review for a venue
            Route::delete('/delete/{id}', [VenueReviewController::class, 'destroy']); // Delete a review
        });
    });


    // Event routes
    Route::prefix('event')->group(function () {
        //event page route according to prototype
        Route::get('/', [EventPageController::class, 'allEvents']);  // List all events
        Route::get('/upcoming', [EventPageController::class, 'upcomingEvents']);  // Get upcoming events only
        Route::get('/past', [EventPageController::class, 'pastEvents']); // Get pasts events
        Route::get('/my', [EventPageController::class, 'myEvents']); // Get authorized user events
        Route::get('/gallery', [EventPageController::class, 'eventGallery']); //Get event's images

        //event manage
        Route::post('/store', [EventManageController::class, 'store']); // Create a new event
        Route::get('/show/{id}', [EventManageController::class, 'show']); // Get a specific event
        Route::post('/update/{id}', [EventManageController::class, 'update']); // Update an event
        Route::delete('/delete/{id}', [EventManageController::class, 'destroy']); // Delete an event
        Route::post('/change-status/{id}', [EventManageController::class, 'changeStatus']); // Update event status


        //event list/unlike
        Route::group(['prefix' => 'like'], function () {
            Route::post('/{event}', [EventLikeController::class, 'toggleLike']); // Like/Unlike an event
            Route::get('/index/{eventId}', [EventLikeController::class, 'index']); // Get all likes
        });

        // Comment-reply routes
        Route::group(['prefix' => 'comment'], function () {
            Route::post('/store/{eventId}', [EventCommentController::class, 'store']); // Add comment
            Route::get('/list/{eventId}', [EventCommentController::class, 'index']); // Get all comments
            Route::post('/update/{id}', [EventCommentController::class, 'update']); // Edit comment
            Route::delete('/delete/{id}', [EventCommentController::class, 'destroy']);  // Delete comment
        });
    });

    //Notification
    Route::get('/my-notifications', [NotificationController::class, 'allNotifications']); //get all notification
    Route::post('/read-notification/{id}', [NotificationController::class, 'readNotification']); //mark as read single notification
    Route::post('/read-all-notifications', [NotificationController::class, 'readAllNotifications']); //mark as read all notification

    //Dashboard routes
    Route::get('/user-event-stats', [DashboardController::class, 'userEventStats']); // user event stats
    Route::get('/venue-rating-stats', [DashboardController::class, 'venueReviewStats']); // venue rating stats
    // Route::get('/event-duration-stats', [DashboardController::class,'eventDurationStats']); // event duration stats


    //Event calendar manage
    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index']); //get all events of auth user
        Route::post('/store', [CalendarController::class, 'store']); //store event in calenar
        Route::get('/edit/{id}', [CalendarController::class, 'edit']); //edit calendar
        Route::post('/update/{id}', [CalendarController::class, 'update']); //update calendar

        //Event filterring
        Route::get('/filter', [CalendarController::class, 'filter']); //event filtering e.g day, week, month, date
    });
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

/**
 * Upcoming event no auth
 */
Route::get('/upcoming-event', [EventPageController::class, 'upcomingEventsNoAuth']);
Route::get('/venue/popular', [VenuePageController::class, 'popularVenue']);
