<?php

namespace App\Http\Controllers\Api\React\Event;

use Exception;
use App\Models\Event;
use App\Helper\Helper;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\EventRequest;
use App\Http\Resources\Event\EventResource;
use App\Notifications\EventCreateNotification;

class EventManageController extends Controller
{
    use ApiResponse;

    //store event
    public function store(EventRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $user = auth('api')->user();

            $validated['user_id'] = $user->id;

            if (isset($validated['tags'])) {
                $tags = array_map('trim', $validated['tags']);
                $tags = array_filter($tags);
                $tags = array_unique($tags);
                $validated['tags'] = json_encode(array_values($tags));
            }

            if ($request->hasFile('banner')) {
                $validated['banner'] = Helper::uploadImage($request->file('banner'), 'event/banners');
            }

            // Create Event
            $event = Event::create($validated);

            // Notify user when event is created
            try {
                $user->notify(new EventCreateNotification($event));
            } catch (Exception $e) {
                Log::error('Notification error: ' . $e->getMessage());
            }

            // Ticket handling
            $ticketData = $request->input('ticket');

            if (is_array($ticketData)) {
                $ticketData['event_id'] = $event->id;
                Ticket::create($ticketData);
            } else {
                // Insert a blank/default ticket for this event
                Ticket::create([
                    'event_id' => $event->id,
                    'ticket_name' => null,
                    'price' => 0,
                    'capacity_type' => 'shared',
                    'shared_capacity' => null,
                    'independent_capacity' => null,
                    'attendee_collection' => 'none'
                ]);
            }

            DB::commit();

            return $this->success([
                'event' => new EventResource($event)
            ], 'Event created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to create event.' . $e->getMessage(), 500, $e);
        }
    }

    //get single event by id
    public function show($id)
    {
        try {

            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $event = Event::with('venue')->find($id);

            if (!$event) {
                return $this->error([], 'Event not found', 404);
            }

            return $this->success([
                'event' => new EventResource($event)
            ], 'Event fetched successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch events. ' . $e->getMessage(), 500);
        }
    }

    //update event
    public function update(EventRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = auth('api')->user();

            $event = Event::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$event) {
                return $this->error([], 'Event not found or unauthorized', 404);
            }

            $validated = $request->validated();

            // Tags cleanup
            if (isset($validated['tags'])) {
                $tags = array_map('trim', $validated['tags']);
                $tags = array_filter($tags);
                $tags = array_unique($tags);
                $validated['tags'] = json_encode(array_values($tags));
            }

            // Banner image upload + delete old
            if ($request->hasFile('banner')) {
                if ($event->banner) {
                    Helper::deleteImage($event->banner);
                }

                $validated['banner'] = Helper::uploadImage(
                    $request->file('banner'),
                    'event/banners'
                );
            }

            // Update event
            $event->update($validated);

            // Ticket update or create
            $ticketData = $request->input('ticket');

            if (is_array($ticketData)) {
                $existingTicket = Ticket::where('event_id', $event->id)->first();

                if ($existingTicket) {
                    $existingTicket->update($ticketData);
                } else {
                    $ticketData['event_id'] = $event->id;
                    Ticket::create($ticketData);
                }
            }

            DB::commit();

            return $this->success(
                new EventResource($event->fresh()),
                'Event updated successfully'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to update event.' . $e->getMessage(), 500, $e);
        }
    }

    //delete event
    public function destroy($id)
    {
        $user = auth('api')->user();
        $event = Event::where('id', $id)->where('user_id', $user->id)->first();

        if (!$event) {
            return $this->error([], 'Event not found or unauthorized', 404);
        }

        if ($event->banner) {
            Helper::deleteImage($event->banner);
        }

        $event->delete();

        return $this->success([], 'Event deleted successfully');
    }

    //change event status
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:going_live,pending,postponed,cancelled,completed'
        ]);

        $user = auth('api')->user();
        $event = Event::where('id', $id)->where('user_id', $user->id)->first();

        if (!$event) {
            return $this->error([], 'Event not found or unauthorized', 404);
        }

        $event->status = $request->status;
        $event->save();

        return $this->success(new EventResource($event), 'Event status updated successfully');
    }
}
