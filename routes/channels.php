<?php

use App\Models\Room;
use App\Models\ChatParticipant;
use Illuminate\Support\Facades\Broadcast;

/* Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}); */

Broadcast::channel('test-notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
# chat
*/

// Broadcast::channel('chat-room.{room_id}', function ($user, $room_id) {
//     $room = Room::find($room_id);
//     return (int) $user->id === (int) $room?->user_one_id || (int) $user->id === (int) $room?->user_two_id;
// });

// Broadcast::channel('chat-receiver.{receiver_id}', function ($user, $receiver_id) {
//     return (int) $user->id === (int) $receiver_id;
// });

// Broadcast::channel('chat-sender.{sender_id}', function ($user, $sender_id) {
//     return (int) $user->id === (int) $sender_id;
// });


// Broadcasting authentication routes
Broadcast::channel('chat-room.{roomId}', function ($user, $roomId) {
    // Check if user is participant of this room
    return ChatParticipant::where('room_id', $roomId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('chat-receiver.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('user-status', function ($user) {
    return true; // Public channel for online status
});
