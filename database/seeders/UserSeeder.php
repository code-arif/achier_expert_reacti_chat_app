<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Admin User ---
        User::create([
            'first_name' => 'System',
            'last_name' => 'Admin',
            'username' => 'admin',
            'role' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'avatar' => 'default/admin_avatar.png',
            'cover' => 'default/admin_cover.jpg',
            'bio' => 'Administrator of the platform.',
            'address' => 'Dhaka, Bangladesh',
            'otp' => null,
            'otp_expires_at' => null,
            'otp_verified_at' => Carbon::now(),
            'status' => 'active',
            'is_google_signin' => false,
            'is_apple_signin' => false,
        ]);

        // --- 3 Regular Users ---
        $users = [
            [
                'first_name' => 'Arif',
                'last_name' => 'Hossain',
                'email' => 'a@gmail.com',
            ],
            [
                'first_name' => 'john',
                'last_name' => 'doe',
                'email' => 'john@doe.com',
            ],
            [
                'first_name' => 'jimmi',
                'last_name' => 'doe',
                'email' => 'jimmi@doe.com',
            ],
            [
                'first_name' => 'rahat',
                'last_name' => 'khan',
                'email' => 'rahat@khan.com',
            ],
            [
                'first_name' => 'rabbi',
                'last_name' => 'alam',
                'email' => 'rabbi@alam.com',
            ],
            [
                'first_name' => 'hossain',
                'last_name' => 'ali',
                'email' => 'hossain@ali.com',
            ],
            [
                'first_name' => 'sandam',
                'last_name' => 'hossain',
                'email' => 'sadman@hossain.com',
            ],
            [
                'first_name' => 'sagar',
                'last_name' => 'mahmud',
                'email' => 'sagar@mahmud.com',
            ],
        ];

        foreach ($users as $user) {
            User::create([
                ...$user,
                'role' => 'user',
                'username' => strtolower($user['first_name'] . $user['last_name']),
                'password' => Hash::make('12345678'),
                'avatar' => 'default/default_person.jpg',
                'cover' => 'default/default_cover.jpg',
                'bio' => 'Hello! I’m ' . $user['first_name'] . '.',
                'address' => 'Dhaka, Bangladesh',
                'otp_verified_at' => Carbon::now(),
                'status' => 'active',
                'is_google_signin' => false,
                'is_apple_signin' => false,
            ]);
        }
    }
}
