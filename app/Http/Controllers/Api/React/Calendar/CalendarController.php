<?php

namespace App\Http\Controllers\Api\React\Calendar;

use Exception;
use Carbon\Carbon;
use App\Models\Calendar;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Calender\CalenderRequest;
use App\Http\Resources\Calender\CalenderResource;

class CalendarController extends Controller
{
    use ApiResponse;

    // Get all calendar events of the authenticated user
    public function index()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $events = Calendar::where('user_id', $user->id)
                ->latest('start_date')
                ->get();

            return $this->success(
                CalenderResource::collection($events),
                'User calendar events retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch calendar events. ' . $e->getMessage(), 500);
        }
    }

    // Store new calendar event
    public function store(CalenderRequest $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $event = Calendar::create([
                'user_id'     => $user->id,
                'event_id'    => $request->event_id,
                'title'       => $request->title,
                'description' => $request->description,
                'all_day'     => $request->all_day ?? false,
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'color_code'  => $request->color_code,
            ]);

            return $this->success(
                new CalenderResource($event),
                'Calendar event created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to create calendar event. ' . $e->getMessage(), 500);
        }
    }

    //Edit calender
    public function edit($id)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $calendar = Calendar::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$calendar) {
                return $this->error([], 'Calendar event not found.', 404);
            }

            return $this->success(
                new CalenderResource($calendar),
                'Calendar event retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch calendar event. ' . $e->getMessage(), 500);
        }
    }


    // Update calendar event
    public function update(CalenderRequest $request, $id)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $calendar = Calendar::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$calendar) {
                return $this->error([], 'Calendar event not found.', 404);
            }

            $calendar->update([
                'event_id'    => $request->event_id,
                'title'       => $request->title,
                'description' => $request->description,
                'all_day'     => $request->all_day ?? false,
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'color_code'  => $request->color_code,
            ]);

            return $this->success(
                new CalenderResource($calendar),
                'Calendar event updated successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to update calendar event. ' . $e->getMessage(), 500);
        }
    }

    public function filter(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $request->validate([
                'view' => 'nullable',
                'date' => 'required|date',
            ]);

            $date = Carbon::parse($request->date);
            $view = $request->view;

            $query = Calendar::with('event.venue')->where('user_id', $user->id);

            if ($view === 'day') {
                $query->whereDate('start_date', $date->toDateString());
            } elseif ($view === 'week') {
                $query->whereBetween('start_date', [
                    $date->copy()->startOfWeek(),
                    $date->copy()->endOfWeek()
                ]);
            } elseif ($view === 'month') {
                $query->whereMonth('start_date', $date->month)
                    ->whereYear('start_date', $date->year);
            } else {
                $query->whereBetween('start_date', [
                    $date->copy()->startOfMonth()->toDateString(),
                    $date->copy()->endOfMonth()->toDateString()
                ]);
            }

            $events = $query->orderBy('start_date')->get();

            return $this->success(
                CalenderResource::collection($events),
                'Filtered calendar events retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to filter calendar events. ' . $e->getMessage(), 500);
        }
    }
}
