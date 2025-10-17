<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'f_name' => $this->faker->firstName,
            'l_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => static::$password ??= Hash::make('password'),
            'role' => $this->faker->randomElement(['user', 'dj', 'promoter', 'artist', 'venue']),
            'avatar' => null,
            'is_otp_verified' => $this->faker->boolean(80),
            'email_verified_at' => now(),
            'profession' => $this->faker->jobTitle,
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'age' => (string) $this->faker->numberBetween(18, 50),
            'address' => $this->faker->streetAddress,
            'country' => $this->faker->country,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'zip_code' => $this->faker->postcode,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'get_notification' => $this->faker->boolean(60),
            'remember_token' => Str::random(10),
        ];
    }
}
