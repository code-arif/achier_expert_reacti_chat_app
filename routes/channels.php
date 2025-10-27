<?php

use App\Models\Room;
use App\Models\Group;
use Illuminate\Support\Facades\Log;
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

Broadcast::channel('chat-room.{room_id}', function ($user, $room_id) {
    $room = Room::find($room_id);
    return (int) $user->id === (int) $room?->user_one_id || (int) $user->id === (int) $room?->user_two_id;
});

Broadcast::channel('chat-receiver.{receiver_id}', function ($user, $receiver_id) {
    return (int) $user->id === (int) $receiver_id;
});

Broadcast::channel('chat-sender.{sender_id}', function ($user, $sender_id) {
    return (int) $user->id === (int) $sender_id;
});

// New group channel
// Broadcast::channel('group-message.{userId}', function ($user, $userId) {
//     return (int) $user->id === (int) $userId;
// });

Broadcast::channel('group-message.{userId}', function ($user, $userId) {
    Log::info('Broadcasting auth attempt', [
        'user_id' => $user->id,
        'channel_user_id' => $userId,
        'match' => (int) $user->id === (int) $userId
    ]);

    return (int) $user->id === (int) $userId;
});
