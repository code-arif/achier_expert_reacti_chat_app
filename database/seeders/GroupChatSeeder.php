<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\GroupMessageRead;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupChatSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $userIds = User::pluck('id')->toArray();
            $groupCount = 5; // you can change to any number you want

            for ($i = 0; $i < $groupCount; $i++) {
                // Random group creator
                $creatorId = $userIds[array_rand($userIds)];

                // Create group
                $group = Group::create([
                    'name' => fake()->words(2, true),
                    'description' => fake()->sentence(),
                    'avatar' => null,
                    'created_by' => $creatorId,
                ]);

                // Select random group members (3–8 users)
                $members = fake()->randomElements($userIds, rand(3, 8));

                // Ensure creator is also in members
                if (!in_array($creatorId, $members)) {
                    $members[] = $creatorId;
                }

                // Add group members
                foreach ($members as $memberId) {
                    GroupMember::create([
                        'group_id' => $group->id,
                        'user_id' => $memberId,
                        'role' => $memberId === $creatorId ? 'admin' : 'member',
                        'joined_at' => now()->subDays(rand(1, 30)),
                    ]);
                }

                // Generate random messages (5–15 per group)
                $messageCount = rand(5, 15);
                for ($j = 0; $j < $messageCount; $j++) {
                    $senderId = fake()->randomElement($members);

                    $message = GroupMessage::create([
                        'group_id' => $group->id,
                        'sender_id' => $senderId,
                        'text' => fake()->sentence(),
                        'file' => null,
                    ]);

                    // Randomly select who read this message
                    $readUsers = fake()->randomElements($members, rand(1, count($members)));
                    foreach ($readUsers as $readerId) {
                        GroupMessageRead::create([
                            'group_message_id' => $message->id,
                            'user_id' => $readerId,
                            'read_at' => now()->subMinutes(rand(1, 60)),
                        ]);
                    }
                }
            }
        });
    }
}
