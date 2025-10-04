<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'phone' => '01700000000',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create 5 Normal Users
        $users = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'email' => 'johndoe@example.com',
                'phone' => '01700000001',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'username' => 'janesmith',
                'email' => 'janesmith@example.com',
                'phone' => '01700000002',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'username' => 'michaelbrown',
                'email' => 'michaelbrown@example.com',
                'phone' => '01700000003',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Johnson',
                'username' => 'emilyjohnson',
                'email' => 'emilyjohnson@example.com',
                'phone' => '01700000004',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'username' => 'davidwilson',
                'email' => 'davidwilson@example.com',
                'phone' => '01700000005',
            ],
        ];

        foreach ($users as $user) {
            User::create([
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]);
        }
    }
}
