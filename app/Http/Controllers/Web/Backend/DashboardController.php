<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use App\Models\Venue;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     */
    public function index()
    {
        $user = auth()->user();
        $totalUsers = User::count();
        return view('backend.layouts.dashboard', compact(
            'totalUsers',
        ));
    }

   
}
