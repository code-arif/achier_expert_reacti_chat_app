<?php

use App\Models\Room;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;

/*
 * Single Chat Channels
 */

Broadcast::channel('chat-room.{room_id}', function ($user, $room_id) {
    $room = Room::find($room_id);
    Log::info('Chat room auth', ['user' => $user->id, 'room' => $room_id]);
    return (int) $user->id === (int) $room?->user_one_id || (int) $user->id === (int) $room?->user_two_id;
});

Broadcast::channel('chat-receiver.{receiver_id}', function ($user, $receiver_id) {
    Log::info('Chat receiver auth', ['user' => $user->id, 'receiver' => $receiver_id]);
    return (int) $user->id === (int) $receiver_id;
});

Broadcast::channel('chat-sender.{sender_id}', function ($user, $sender_id) {
    return (int) $user->id === (int) $sender_id;
});

/*
 * Group Chat Channel - ✅ FIXED
 */
Broadcast::channel('group-message.{userId}', function ($user, $userId) {
    Log::info('🔐 Group channel auth attempt', [
        'authenticated_user' => $user->id,
        'channel_user_id' => $userId,
        'match' => (int) $user->id === (int) $userId,
        'user_email' => $user->email,
    ]);

    // Must return true/false or user data
    $isAuthorized = (int) $user->id === (int) $userId;

    if ($isAuthorized) {
        Log::info('✅ Group channel authorized', ['user_id' => $user->id]);
        return ['id' => $user->id, 'name' => $user->first_name];
    }

    Log::warning('❌ Group channel unauthorized', ['user_id' => $user->id]);
    return false;
});

/*
 * Notification Channels
 */
Broadcast::channel('notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('test-notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
