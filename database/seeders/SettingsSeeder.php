<?php

namespace Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Settings::create([
            'name'        => 'Stack Master',
            'title'       => 'Stack Master - Digital Innovation Agency',
            'description' => "Stack Master is a digital agency focused on creating and delivering innovative digital product experiences.
                      We specialize in helping startups and small businesses with project updates, creative solutions,
                      and industry insights—giving users a behind-the-scenes look at how digital ideas come to life.",

            'keywords'    => 'Stack Master, Digital Agency, Startups, Creative Solutions, Innovation',
            'author'      => 'Ariful Islam',

            'phone'       => '+880123456789',
            'additional_phone' => '+8801987654321',

            'email'       => 'info@stack.us',
            'additional_email' => 'support@stack.us',

            'address'     => 'Dhaka, Bangladesh',

            'copyright'   => '© 2025 Stack Master. All rights reserved.',

            'logo'        => 'uploads/settings/logo.png',
            'logo_height' => 60,
            'logo_width'  => 200,

            'favicon'     => 'uploads/settings/favicon.ico',

            'signature'   => 'Thank you for being with Stack Master!',
        ]);
    }
}
