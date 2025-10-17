<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = 1;

        // Pick 20 unique users from the rest (2 to 50)
        $userTwoIds = range(2, 50);
        shuffle($userTwoIds);
        $userTwoIds = array_slice($userTwoIds, 0, 20);

        $rooms = [];

        foreach ($userTwoIds as $userTwoId) {
            $rooms[] = [
                'user_one_id' => $adminId,
                'user_two_id' => $userTwoId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('rooms')->insert($rooms);
    }
}
