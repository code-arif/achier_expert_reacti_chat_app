<?php

namespace Database\Seeders;

use App\Models\UserProfile;
use Illuminate\Database\Seeder;


class UserProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'user_id' => 2,
                'avatar' => 'avatars/john.jpg',
                'cover_photo' => 'covers/john-cover.jpg',
                'bio' => 'Love exploring new places and meeting new people.',
                'tagline' => 'Adventure Seeker',
                'date_of_birth' => '1995-06-15',
                'gender' => 'male',
                'city' => 'New York',
                'country' => 'USA',
                'profile_visibility' => 'public',
                'show_email' => true,
                'show_phone' => false,
                'show_birthday' => true,
                'reaction_count' => 120,
                'friends_count' => 45,
                'group_count' => 3,
            ],
            [
                'user_id' => 3,
                'avatar' => 'avatars/jane.jpg',
                'cover_photo' => 'covers/jane-cover.jpg',
                'bio' => 'Coffee lover & bookworm. Coding by day, Netflix by night.',
                'tagline' => 'Keep it simple',
                'date_of_birth' => '1997-11-02',
                'gender' => 'female',
                'city' => 'Los Angeles',
                'country' => 'USA',
                'profile_visibility' => 'friends',
                'show_email' => false,
                'show_phone' => true,
                'show_birthday' => false,
                'reaction_count' => 250,
                'friends_count' => 80,
                'group_count' => 5,
            ],
            [
                'user_id' => 4,
                'avatar' => 'avatars/michael.jpg',
                'cover_photo' => 'covers/michael-cover.jpg',
                'bio' => 'Tech enthusiast, gamer, and blogger.',
                'tagline' => 'Work hard, play harder',
                'date_of_birth' => '1994-03-21',
                'gender' => 'male',
                'city' => 'Chicago',
                'country' => 'USA',
                'profile_visibility' => 'public',
                'show_email' => false,
                'show_phone' => false,
                'show_birthday' => true,
                'reaction_count' => 340,
                'friends_count' => 120,
                'group_count' => 8,
            ],
            [
                'user_id' => 5,
                'avatar' => 'avatars/emily.jpg',
                'cover_photo' => 'covers/emily-cover.jpg',
                'bio' => 'Music and travel is my therapy.',
                'tagline' => 'Spread positivity',
                'date_of_birth' => '1998-07-10',
                'gender' => 'female',
                'city' => 'London',
                'country' => 'UK',
                'profile_visibility' => 'private',
                'show_email' => false,
                'show_phone' => false,
                'show_birthday' => false,
                'reaction_count' => 90,
                'friends_count' => 30,
                'group_count' => 2,
            ],
            [
                'user_id' => 6,
                'avatar' => 'avatars/david.jpg',
                'cover_photo' => 'covers/david-cover.jpg',
                'bio' => 'Fitness trainer and motivational speaker.',
                'tagline' => 'No pain, no gain',
                'date_of_birth' => '1992-12-05',
                'gender' => 'male',
                'city' => 'Sydney',
                'country' => 'Australia',
                'profile_visibility' => 'public',
                'show_email' => true,
                'show_phone' => true,
                'show_birthday' => true,
                'reaction_count' => 500,
                'friends_count' => 200,
                'group_count' => 10,
            ],
        ];

        foreach ($profiles as $profile) {
            UserProfile::create($profile);
        }
    }
}
