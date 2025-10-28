<?php


use App\Http\Controllers\Web\Backend\AdminGroupChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\ChatManageController;
use App\Http\Controllers\Web\Backend\Settings\SocialController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\Settings\FirebaseController;
use App\Http\Controllers\Web\Backend\Settings\DynamicPageController;


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard data for charts
    Route::get('dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');


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

    // Group Chat Routes
    Route::prefix('group')->name('group.')->group(function () {
        Route::get('/chat', [AdminGroupChatController::class, 'index'])->name('chat');
        Route::get('/users/list', [AdminGroupChatController::class, 'getUsersList'])->name('users.list');
        Route::post('/create', [AdminGroupChatController::class, 'createGroup'])->name('create');
        Route::get('/list', [AdminGroupChatController::class, 'listGroups'])->name('list');
        Route::get('/{group_id}', [AdminGroupChatController::class, 'groupDetails'])->name('details');
        Route::post('/{group_id}/send', [AdminGroupChatController::class, 'sendMessage'])->name('send.message');
        Route::post('/{group_id}/message/{message_id}', [AdminGroupChatController::class, 'editMessage'])->name('edit.message');
        Route::get('/{group_id}/messages', [AdminGroupChatController::class, 'getMessages'])->name('conversations');
        Route::post('/{group_id}/read', [AdminGroupChatController::class, 'markAsRead'])->name('message.mark.as.read');
        Route::post('/{group_id}/add-members', [AdminGroupChatController::class, 'addMembers'])->name('add.member');
        Route::delete('/{group_id}/remove-member/{user_id}', [AdminGroupChatController::class, 'removeMember'])->name('remove.member');
        Route::post('/{group_id}/make-admin/{user_id}', [AdminGroupChatController::class, 'makeAdmin'])->name('make.admin');
        Route::post('/{group_id}/leave', [AdminGroupChatController::class, 'leaveGroup'])->name('leave');
        Route::delete('/{group_id}/delete', [AdminGroupChatController::class, 'deleteGroup'])->name('delete');
        Route::delete('/{group_id}/delete-messages', [AdminGroupChatController::class, 'deleteMessages'])->name('delete.messages');
        Route::post('/{group_id}/update', [AdminGroupChatController::class, 'updateGroup'])->name('update.group');
    });
});



//! Route for Profile Settings
Route::controller(ProfileController::class)->group(function () {
    Route::get('setting/profile', 'index')->name('setting.profile.index');
    Route::put('setting/profile/update', 'UpdateProfile')->name('setting.profile.update');
    Route::put('setting/profile/update/Password', 'UpdatePassword')->name('setting.profile.update.Password');
    Route::post('setting/profile/update/Picture', 'UpdateProfilePicture')->name('update.profile.picture');
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


Route::controller(DynamicPageController::class)->group(function () {
    Route::get('/dynamic-page', 'index')->name('admin.dynamic_page.index');
    Route::get('/dynamic-page/create', 'create')->name('admin.dynamic_page.create');
    Route::post('/dynamic-page/store', 'store')->name('admin.dynamic_page.store');
    Route::get('/dynamic-page/edit/{id}', 'edit')->name('admin.dynamic_page.edit');
    Route::put('/dynamic-page/update/{id}', 'update')->name('admin.dynamic_page.update');
    Route::post('/dynamic-page/status/{id}', 'status')->name('admin.dynamic_page.status');
    Route::delete('/dynamic-page/destroy/{id}', 'destroy')->name('admin.dynamic_page.destroy');
});
