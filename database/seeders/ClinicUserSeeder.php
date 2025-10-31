<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClinicUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test clinic account
        User::create([
            'name' => 'Test Clinic',
            'email' => 'clinic@example.com',
            'password' => Hash::make('password'),
            'account_type' => 'clinic',
            'email_verified_at' => now(),
        ]);

        // Create a test user account
        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'account_type' => 'user',
            'email_verified_at' => now(),
        ]);
    }
}
