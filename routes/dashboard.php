<?php

use App\Http\Controllers\Web\Admin\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

// show dahboard page
Route::get('/database', [DashboardController::class, 'index'])->name('show.dashboard.page');
