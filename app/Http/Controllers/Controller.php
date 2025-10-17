<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

abstract class Controller
{
    public function __construct()
    {
        if(!Session::has('locale')) {
            $locale = 'en';
            Session::put('locale' , $locale);
            App::setLocale($locale);
        }

        if(!Session::has('timezone')) {
            Session::put('timezone' , 'UTC');
        }

        if (auth('web')->check()) {
            auth('web')->user()->update(['last_activity_at' => now()]);
        }

        if (auth('api')->check()) {
            auth('api')->user()->update(['last_activity_at' => now()]);
        }
    }
}
