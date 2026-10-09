<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'level' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        // Regular test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'level' => 'USER',
            'email_verified_at' => now(),
        ]);
    }
}
