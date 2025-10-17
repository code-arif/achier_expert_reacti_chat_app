<?php

namespace App\Http\Controllers\Api\React\Event;

use Exception;
use Carbon\Carbon;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Event\EventResource;
use App\Http\Resources\Event\EventCollection;
use App\Http\Resources\Event\EventLimitResource;

class EventPageController extends Controller
{
    use ApiResponse;

    //get all events
    public function allEvents(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $events = Event::with(['venue', 'user']);

            // Search by title or venue
            if ($query = $request->query('title')) {
                $events->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhereHas('venue', fn($q2) => $q2->where('title', 'like', "%{$query}%"));
                });
            }

            // Filter by nearby date range
            if ($date = $request->query('date')) {
                $baseDate = Carbon::parse($date);
                $start = $baseDate->copy()->subDays(2);
                $end = $baseDate->copy()->addDays(5);

                $events->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start);
            }

            // filter by status (upcoming, past, completed)
            if ($status = $request->query('status')) {
                $now = Carbon::now();
                if ($status === 'upcoming') {
                    $events->where('start_date', '>=', $now);
                } elseif ($status === 'past') {
                    $events->where('end_date', '<', $now);
                } elseif ($status === 'completed') {
                    $events->where('status', 'completed');
                }
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $events = $events->orderBy('start_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->paginate($perPage);

            return $this->success(
                new EventCollection($events),
                'Events fetched successfully.'
            );
        } catch (Exception $e) {
            Log::error('All events error: ' . $e->getMessage());
            return $this->error([], 'Failed to fetch events. ' . $e->getMessage(), 500);
        }
    }

    // upcoming events
    public function upcomingEvents(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $now = Carbon::now();
            $perPage = $request->input('per_page', 10);

            $events = Event::with(['venue', 'user'])
                ->where('user_id', $user->id)
                ->where(function ($query) use ($now) {
                    $query->where('start_date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('start_date', '=', $now->toDateString())
                                ->where('start_time', '>=', $now->toTimeString());
                        });
                });

            // Search by title or venue
            if ($query = $request->query('title')) {
                $events->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhereHas('venue', fn($q2) => $q2->where('title', 'like', "%{$query}%"));
                });
            }

            // Filter by nearby date range
            if ($date = $request->query('date')) {
                $baseDate = Carbon::parse($date);
                $start = $baseDate->copy()->subDays(2);
                $end = $baseDate->copy()->addDays(5);

                $events->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start);
            }

            // Sorting
            $events = $events->orderBy('start_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->paginate($perPage);

            return $this->success(
                new EventCollection($events),
                'Upcoming events fetched successfully.'
            );
        } catch (Exception $e) {
            Log::error('Upcoming events error: ' . $e->getMessage());
            return $this->error([], 'Failed to fetch upcoming events. ' . $e->getMessage(), 500);
        }
    }

    //up coming events no auth
    public function upcomingEventsNoAuth(Request $request)
    {
        try {
            $now = Carbon::now();

            $events = Event::with(['venue'])
                ->where(function ($query) use ($now) {
                    $query->where('start_date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('start_date', '=', $now->toDateString())
                                ->where('start_time', '>=', $now->toTimeString());
                        });
                })
                ->limit(3)
                ->get();

            return $this->success(
                EventLimitResource::collection($events),
                'Upcoming events fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch upcoming events. ' . $e->getMessage(), 500);
        }
    }

    // past events
    public function pastEvents(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $perPage = $request->input('per_page', 10);
            $now = Carbon::now();

            $events = Event::with(['venue', 'user'])
                ->where('end_date', '<', $now);

            // Search by title or venue
            if ($query = $request->query('title')) {
                $events->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhereHas('venue', fn($q2) => $q2->where('title', 'like', "%{$query}%"));
                });
            }

            // Filter by nearby date range
            if ($date = $request->query('date')) {
                $baseDate = Carbon::parse($date);
                $start = $baseDate->copy()->subDays(2);
                $end = $baseDate->copy()->addDays(5);

                $events->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start);
            }

            $events = $events->orderBy('start_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->paginate($perPage);

            return $this->success(
                new EventCollection($events),
                'Past events fetched successfully.'
            );
        } catch (Exception $e) {
            Log::error('Past events error: ' . $e->getMessage());
            return $this->error([], 'Failed to fetch past events. ' . $e->getMessage(), 500);
        }
    }

    //get auth user all events
    public function myEvents(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $perPage = $request->input('per_page', 10);
            $events = Event::with(['venue', 'user'])
                ->where('user_id', $user->id);

            // Search by title or venue
            if ($query = $request->query('title')) {
                $events->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhereHas('venue', fn($q2) => $q2->where('title', 'like', "%{$query}%"));
                });
            }

            // Filter by nearby date range
            if ($date = $request->query('date')) {
                $baseDate = Carbon::parse($date);
                $start = $baseDate->copy()->subDays(2);
                $end = $baseDate->copy()->addDays(5);

                $events->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start);
            }

            // Filter by status (upcoming, past, completed)
            if ($status = $request->query('status')) {
                $now = Carbon::now();
                if ($status === 'upcoming') {
                    $events->where('start_date', '>=', $now);
                } elseif ($status === 'past') {
                    $events->where('end_date', '<', $now);
                } elseif ($status === 'completed') {
                    $events->where('status', 'completed');
                }
            }

            // Sorting
            $events = $events->orderBy('start_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->paginate($perPage);

            return $this->success(
                new EventCollection($events),
                'User events fetched successfully.'
            );
        } catch (Exception $e) {
            Log::error('My events error: ' . $e->getMessage());
            return $this->error([], 'Failed to fetch events. ' . $e->getMessage(), 500);
        }
    }

    //get events images
    public function eventGallery()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $events = Event::select('id', 'banner')->inRandomOrder()->limit(12)->get()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'banner_url' => $event->banner ? asset('/' . $event->banner) : null,
                ];
            });

            return $this->success([
                'gallery' => $events
            ], "Event's images fetched successfully.");
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch images. ' . $e->getMessage(), 500);
        }
    }
}
