<?php

namespace App\Http\Controllers\Api\React;

use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\Event;
use App\Models\Venue;
use App\Models\VenueReview;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    use ApiResponse;

    //user events stats
    public function userEventStats()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Total events created by the user
            $totalEvents = Event::where('user_id', $user->id)->count();

            // Last month date range
            $now = Carbon::now();
            $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

            $lastMonthEventCount = Event::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->count();


            //Total posts
            $totalPosts = Post::where('user_id', $user->id)->count();
            //last month post count
            $lastMonthPostCount = Post::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->count();

            // Upcoming events (start_date in future)
            $upcomingEvents = Event::where('user_id', $user->id)
                ->whereDate('start_date', '>', $now->toDateString())
                ->orderBy('start_date')
                ->get();

            $upcomingEventsCount = $upcomingEvents->count();

            // Next upcoming event
            $nextEvent = $upcomingEvents->first();

            $nextEventInDays = null;
            if ($nextEvent) {
                $nextEventInDays = (int) $now->diffInDays(Carbon::parse($nextEvent->start_date));
            }

            //Total completed event
            $completedEvent = Event::where('user_id', $user->id)->where('status', 'completed')->count();

            return $this->success([
                'total_events' => $totalEvents,
                'last_month_events' => $lastMonthEventCount,
                'total_post' => $totalPosts,
                'last_month_posts' => $lastMonthPostCount,
                'upcoming_events_count' => $upcomingEventsCount,
                'next_event_in_days' => $nextEventInDays,
                'completed_event'=> $completedEvent,
            ], 'User event stats fetched successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch stats. ' . $e->getMessage(), 500);
        }
    }

    //venue rating stats
    public function venueReviewStats()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Get all venue IDs owned by this user
            $venueIds = Venue::where('user_id', $user->id)->pluck('id');

            if ($venueIds->isEmpty()) {
                return $this->success([
                    'average_rating' => null,
                    'last_month_review_count' => 0,
                ], 'No venues found.');
            }

            $now = Carbon::now();
            $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

            // Calculate combined average rating
            $averageRating = VenueReview::whereIn('venue_id', $venueIds)->avg('rating');

            // Count last month reviews
            $lastMonthCount = VenueReview::whereIn('venue_id', $venueIds)
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->count();

            return $this->success([
                'average_rating' => round($averageRating, 1),
                'last_month_review_count' => $lastMonthCount,
            ], 'Overall rating stats fetched successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch overall review stats. ' . $e->getMessage(), 500);
        }
    }
}
