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
        $totalEvents = Event::count();
        $totalVenues = Venue::count();
        $totalPosts = Post::count();
        return view('backend.layouts.dashboard', compact(
            'totalUsers',
            'totalEvents',
            'totalVenues',
            'totalPosts'
        ));
    }

    //dashboard data for charts
    public function getDashboardData()
    {
        //user role pie chart data
        $userRoles = User::selectRaw('role, count(*) as count')
            ->where('role', '!=', 'admin')
            ->groupBy('role')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->role => $item->count];
            });

        //new user registeration data
        $newUserRegistrations = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            });

        // Event status pie chart data
        $eventStatuses = Event::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });

        // New events creation line chart (by date)
        $newEventCreations = Event::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            });

        //return JSON response with user role data
        return response()->json([
            //user roles
            'user_roles' => [
                'user' => $userRoles['user'] ?? 0,
                'dj' => $userRoles['dj'] ?? 0,
                'venue' => $userRoles['venue'] ?? 0,
                'promoter' => $userRoles['promoter'] ?? 0,
                'artist' => $userRoles['artist'] ?? 0,
            ],

            //new user registrations
            'new_user_registrations' => $newUserRegistrations,

            //event counts
            'event_statuses' => [
                'going_live' => $eventStatuses['going_live'] ?? 0,
                'pending'    => $eventStatuses['pending'] ?? 0,
                'postponed'  => $eventStatuses['postponed'] ?? 0,
                'cancelled'  => $eventStatuses['cancelled'] ?? 0,
                'completed'  => $eventStatuses['completed'] ?? 0,
            ],
            //new event creations
            'new_event_creations' => $newEventCreations,
        ]);
    }
}
