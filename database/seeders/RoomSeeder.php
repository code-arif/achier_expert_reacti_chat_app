<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Room;
use App\Models\Chat;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $userIds = User::pluck('id')->toArray();

        // Create 10 unique rooms
        for ($i = 0; $i < 10; $i++) {
            // Randomly pick two different users
            $userOne = $userIds[array_rand($userIds)];
            do {
                $userTwo = $userIds[array_rand($userIds)];
            } while ($userTwo === $userOne);

            // Ensure unique combination (avoid duplicate rooms)
            $exists = Room::where(function ($q) use ($userOne, $userTwo) {
                $q->where('user_one_id', $userOne)->where('user_two_id', $userTwo);
            })->orWhere(function ($q) use ($userOne, $userTwo) {
                $q->where('user_one_id', $userTwo)->where('user_two_id', $userOne);
            })->exists();

            if ($exists) continue;

            // Create Room
            $room = Room::create([
                'user_one_id' => $userOne,
                'user_two_id' => $userTwo,
            ]);

            // Generate 5–10 random chat messages per room
            $messageCount = rand(5, 10);
            for ($j = 0; $j < $messageCount; $j++) {
                $sender = rand(0, 1) ? $userOne : $userTwo;
                $receiver = $sender === $userOne ? $userTwo : $userOne;

                Chat::create([
                    'sender_id' => $sender,
                    'receiver_id' => $receiver,
                    'room_id' => $room->id,
                    'text' => fake()->sentence(),
                    'file' => null,
                    'status' => fake()->randomElement(['sent', 'delivered', 'read']),
                    'is_blurred' => fake()->boolean(10),
                    'is_viewed' => fake()->boolean(70),
                    'message_type' => fake()->randomElement(['normal', 'reaction']),
                ]);
            }
        }
    }
}
