<?php

use App\Http\Controllers\Web\Admin\Auth\AuthController;
use App\Http\Controllers\Web\Admin\Auth\ForgetPasswordController;
use App\Http\Controllers\Web\Admin\Auth\PasswordUpdateController;
use App\Http\Controllers\Web\Admin\Auth\ProfileController;
use Illuminate\Support\Facades\Route;

// guest mode admin panel
Route::middleware('guest')->group(function () {

    // admin login
    Route::post('login', [AuthController::class, 'login'])->name('admin.login');

    // show forget passwrod page
    Route::get('forgot-password', [ForgetPasswordController::class, 'create'])->name('show.forget.password');

    // forget password
    Route::post('forgot-password', [ForgetPasswordController::class, 'store'])->name('password.email');

    // show reset password
    Route::get('reset-password/{token}', [ForgetPasswordController::class, 'create'])->name('show.reset.password');

    // set new password
    Route::post('reset-password', [ForgetPasswordController::class, 'store'])->name('password.store');
});


// auth mode admin panel
Route::middleware('auth')->group(function () {
    // show password update page
    Route::get('password-update', [PasswordUpdateController::class, 'create'])->name('show.password.update');
    Route::put('password-update', [PasswordUpdateController::class, 'update'])->name('password.update');

    // profile manage
    Route::get('profile', [ProfileController::class, 'show'])->name('show.profile');
    Route::post('update', [ProfileController::class, 'update'])->name('update.profile');

    // update admin avatar
    Route::get('avatar', [ProfileController::class, 'show'])->name('show.avatar');
    Route::post('avatar', [ProfileController::class, 'update'])->name('update.avatar');


    // logout
    Route::post('logout', [AuthController::class, 'destroy'])
        ->name('logout');
});
