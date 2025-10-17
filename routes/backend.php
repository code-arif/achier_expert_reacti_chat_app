<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\FaqController;
use App\Http\Controllers\Web\Backend\ChatController;
use App\Http\Controllers\Web\Backend\CMS\HomeController;
use App\Http\Controllers\Web\Backend\PostListController;
use App\Http\Controllers\Web\Backend\UserListController;
use App\Http\Controllers\Web\Backend\CMS\EventController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\EventListController;
use App\Http\Controllers\Web\Backend\VenueManageController;
use App\Http\Controllers\Web\Backend\ChatManageController;
use App\Http\Controllers\Web\Backend\CMS\FeatureController;
use App\Http\Controllers\Web\Backend\TestimonialController;
use App\Http\Controllers\Api\React\CMS\NewsletterController;
use App\Http\Controllers\Web\Backend\CMS\AuthPageController;
use App\Http\Controllers\Web\Backend\BusinessProfileController;
use App\Http\Controllers\Web\Backend\Settings\SocialController;
use App\Http\Controllers\Web\Backend\Settings\StripeController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\Settings\FirebaseController;
use App\Http\Controllers\Web\Backend\Settings\DynamicPageController;
use App\Http\Controllers\Web\Backend\Settings\MailSettingController;
use App\Http\Controllers\Web\Backend\Settings\SocialSettingController;


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard data for charts
    Route::get('dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    // cms management
    Route::prefix('cms')->name('cms.')->group(function () {

        // Home Page
        Route::prefix('home')->name('home.')->group(function () {
            // Hero Section
            Route::get('/hero', [HomeController::class, 'hero'])->name('hero');
            Route::post('/hero/update', [HomeController::class, 'updateHero'])->name('hero.update');

            // Upcoming Event Section
            Route::get('/upcoming-events', [HomeController::class, 'upcomingEvents'])->name('event');
            Route::post('/upcoming-events/update', [HomeController::class, 'updateUpcomingEvents'])->name('event.update');

            // Popular Venue Section
            Route::get('/popular-venues', [HomeController::class, 'popularVenues'])->name('venues');
            Route::post('/popular-venues/update', [HomeController::class, 'updatePopularVenues'])->name('venues.update');

            // App Download Section
            Route::get('/app-download', [HomeController::class, 'appDownload'])->name('app.download');
            Route::post('/app-download/update', [HomeController::class, 'updateAppDownload'])->name('app.download.update');
        });

        // Event Page
        Route::prefix('event')->name('event.')->group(function () {
            // Hero Section
            Route::get('/hero', [EventController::class, 'hero'])->name('hero');
            Route::post('/hero/update', [EventController::class, 'updateHero'])->name('hero.update');

            //Up coming event
            Route::get('/upcoming-events', [EventController::class, 'upcomingEvents'])->name('upcoming');
            Route::post('/upcoming-events/update', [EventController::class, 'updateUpcomingEvents'])->name('upcoming.update');

            // Event Details Hero
            Route::get('/details-hero', [EventController::class, 'detailsHero'])->name('details.hero');
            Route::post('/details-hero/update', [EventController::class, 'updateDetailsHero'])->name('details.hero.update');
        });

        // Feature Page
        Route::prefix('feature')->name('feature.')->group(function () {
            // Hero Section
            Route::get('/hero', [FeatureController::class, 'hero'])->name('hero');
            Route::post('/hero/update', [FeatureController::class, 'updateHero'])->name('hero.update');

            // Feature Items
            Route::get('/items', [FeatureController::class, 'index'])->name('items.index');
            Route::post('/items/hero/update', [FeatureController::class, 'updateItemHero'])->name('items.hero.update');
            Route::post('/items', [FeatureController::class, 'store'])->name('items.store');
            Route::get('/items/{id}/edit', [FeatureController::class, 'edit'])->name('items.edit');
            Route::post('/items/{id}/update', [FeatureController::class, 'update'])->name('items.update');
            Route::post('/items/{id}/status', [FeatureController::class, 'toggleStatus'])->name('items.status');
            Route::delete('/items/{id}', [FeatureController::class, 'destroy'])->name('items.destroy');
        });

        // Newsletter Section
        Route::prefix('newsletter')->name('newsletter.')->group(function () {
            Route::get('/', [NewsletterController::class, 'index'])->name('index');
            Route::post('/update', [NewsletterController::class, 'update'])->name('update');
        });
    });

    //users, events, venues, promoters list
    Route::get('/user-list', [UserListController::class, 'index'])->name('admin.user.index');
    Route::get('/event-list', [EventListController::class, 'index'])->name('admin.event.index');
    Route::get('/post-list', [PostListController::class, 'index'])->name('admin.post.index');

    //venue manage
    Route::controller(VenueManageController::class)->prefix('venue')->name('admin.venue.')->group(function () {
        Route::get('/', 'index')->name('index'); // List all venues
        Route::post('/store', 'store')->name('store'); // Store a new venue
        Route::get('/edit/{id}', 'edit')->name('edit'); // Get a venue for editing
        Route::post('/update/{id}', 'update')->name('update'); // Update a venue
        Route::post('/status/{id}', 'status')->name('status'); // Update status only
        Route::delete('/delete/{id}', 'destroy')->name('destroy'); // Delete a venue
    });
});



Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/business-profile/pending', [BusinessProfileController::class, 'pendingProfiles'])->name('admin.business_profile.pending');
    Route::post('/business-profile/approve/{id}', [BusinessProfileController::class, 'approveProfile'])->name('admin.business_profile.approve');
    Route::post('/business-profile/cancel/{id}', [BusinessProfileController::class, 'cancelProfile'])->name('admin.business_profile.cancel');
    Route::get('/business-profile/{id}', [BusinessProfileController::class, 'profileDetails'])->name('admin.business_profile.show');
});


Route::controller(ChatManageController::class)->prefix('chat')->name('admin.chat.')->group(function () {

    Route::get('/', 'index')->name('index');
    Route::get('/list', 'list')->name('list');
    Route::post('/send/{receiver_id}', 'send')->name('send');
    Route::get('/conversation/{receiver_id}', 'conversation')->name('conversation');
    Route::get('/room/{receiver_id}', 'room');
    Route::get('/search', 'search')->name('search');
    Route::get('/seen/all/{receiver_id}', 'seenAll');
    Route::get('/seen/single/{chat_id}', 'seenSingle');
});




Route::controller(FaqController::class)->group(function () {
    Route::get('/faq', 'index')->name('admin.faq.index');
    Route::get('/faq/create', 'create')->name('admin.faq.create');
    Route::post('/faq', 'store')->name('admin.faq.store');
    Route::get('/faq/edit/{id}', 'edit')->name('admin.faq.edit');
    Route::put('/faq/{id}', 'update')->name('admin.faq.update');
    Route::post('/faq/status/{id}', 'status')->name('admin.faq.status');
    Route::delete('/faq/{id}', 'destroy')->name('admin.faq.destroy');
});


Route::get('/testimonials', [TestimonialController::class, 'index'])->name('admin.testimonial.index');
Route::post('/testimonial/status/{id}', [TestimonialController::class, 'status'])->name('admin.testimonial.status');
Route::delete('/testimonial/delete/{id}', [TestimonialController::class, 'destroy'])->name('admin.testimonial.destroy');


Route::get('/admin/social-media-settings', [SocialSettingController::class, 'index'])->name('admin.social_media.index');
Route::get('/admin/social-media/{id}/edit', [SocialSettingController::class, 'edit'])->name('admin.social_media.edit');
Route::put('/admin/social-media/{id}', [SocialSettingController::class, 'update'])->name('admin.social_media.update');





//! Route for Profile Settings
Route::controller(ProfileController::class)->group(function () {
    Route::get('setting/profile', 'index')->name('setting.profile.index');
    Route::put('setting/profile/update', 'UpdateProfile')->name('setting.profile.update');
    Route::put('setting/profile/update/Password', 'UpdatePassword')->name('setting.profile.update.Password');
    Route::post('setting/profile/update/Picture', 'UpdateProfilePicture')->name('update.profile.picture');
});

//! Route for Mail Settings
Route::controller(MailSettingController::class)->group(function () {
    Route::get('setting/mail', 'index')->name('setting.mail.index');
    Route::patch('setting/mail', 'update')->name('setting.mail.update');
});

//! Route for Stripe Settings
Route::controller(StripeController::class)->prefix('setting/stripe')->name('setting.stripe.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::patch('/update', 'update')->name('update');
});

//! Route for Firebase Settings
Route::controller(FirebaseController::class)->prefix('setting/firebase')->name('setting.firebase.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::patch('/update', 'update')->name('update');
});

//! Route for Firebase Settings
Route::controller(SocialController::class)->prefix('setting/social')->name('setting.social.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::patch('/update', 'update')->name('update');
});

//! Route for Stripe Settings
Route::controller(SettingController::class)->group(function () {
    Route::get('setting/general', 'index')->name('setting.general.index');
    Route::patch('setting/general', 'update')->name('setting.general.update');
});


//CMS
Route::controller(AuthPageController::class)->prefix('cms')->name('cms.')->group(function () {
    Route::get('page/auth/section/bg', 'index')->name('page.auth.section.bg.index');
    Route::patch('page/auth/section/bg', 'update')->name('page.auth.section.bg.update');
});


Route::controller(DynamicPageController::class)->group(function () {
    Route::get('/dynamic-page', 'index')->name('admin.dynamic_page.index');
    Route::get('/dynamic-page/create', 'create')->name('admin.dynamic_page.create');
    Route::post('/dynamic-page/store', 'store')->name('admin.dynamic_page.store');
    Route::get('/dynamic-page/edit/{id}', 'edit')->name('admin.dynamic_page.edit');
    Route::put('/dynamic-page/update/{id}', 'update')->name('admin.dynamic_page.update');
    Route::post('/dynamic-page/status/{id}', 'status')->name('admin.dynamic_page.status');
    Route::delete('/dynamic-page/destroy/{id}', 'destroy')->name('admin.dynamic_page.destroy');
});


/*
* Chating Route
*/

Route::controller(ChatController::class)->prefix('chat')->name('admin.chat.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/list', 'list')->name('list');
    Route::post('/send/{receiver_id}', 'send')->name('send');
    Route::get('/conversation/{receiver_id}', 'conversation')->name('conversation');
    Route::get('/room/{receiver_id}', 'room');
    Route::get('/search', 'search')->name('search');
    Route::get('/seen/all/{receiver_id}', 'seenAll');
    Route::get('/seen/single/{chat_id}', 'seenSingle');
    Route::delete('/conversation/delete/{receiver_id}', 'deleteConversation');
});
