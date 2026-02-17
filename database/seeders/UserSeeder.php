<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

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
            'phone' => '1234567890',
            'password' => Hash::make('12345678'),
            'avatar' => asset('default/default_image.jpg'),
            'cover' => asset('default/default_image.jpg'),
            'bio' => 'Administrator of the platform.',
            'address' => 'Dhaka, Bangladesh',
            'otp' => null,
            'otp_expires_at' => null,
            'otp_verified_at' => Carbon::now(),
            'status' => 'active',
            'is_google_signin' => false,
            'is_apple_signin' => false,
        ]);

        // --- 10 Regular Users data
        $faker = Faker::create();

        for ($i = 1; $i <= 10; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $email = strtolower($firstName . '.' . $lastName . $i . '@gmail.com');

            User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => strtolower($firstName . $lastName . $i),
                'role' => 'user',
                'email' => $email,
                'phone' => '01' . $faker->numberBetween(100000000, 999999999),
                'password' => Hash::make('12345678'),
                'avatar' => asset('default/default_image.jpg'),
                'cover' => asset('default/default_image.jpg'),
                'bio' => 'Hello! I’m ' . $firstName . '.',
                'address' => $faker->city . ', Bangladesh',
                'otp_verified_at' => Carbon::now(),
                'status' => 'active',
                'is_google_signin' => false,
                'is_apple_signin' => false,
            ]);
        }
    }
}
