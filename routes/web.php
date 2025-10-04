<?php

use Illuminate\Support\Facades\Route;


// show login page at root path
Route::get('/', function(){
    return view('auth.login');
});

Route::get('/dashboard', function(){
    return view('pages.dashboard.dashboard');
});

Route::get('/general-settings', function(){
    return view('pages.settings.general_settings');
});



require __DIR__.'/dashboard.php';
