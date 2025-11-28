<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding demo user...');
        
        // Create only one demo user
        $userData = [
            'email' => 'demo@petconnect.com',
            'password' => 'password123',
            'profile' => [
                'first_name' => 'Demo',
                'last_name' => 'User',
                'phone' => '09171234567',
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'occupation' => 'Pet Owner',
                'bio' => 'Demo account with sample pets and appointments.',
            ],
            'address' => [
                'address_line_1' => '123 Taft Avenue',
                'address_line_2' => 'Unit 5B',
                'city' => 'Manila',
                'state' => 'Metro Manila',
                'postal_code' => '1000',
                'latitude' => 14.5995,
                'longitude' => 120.9842,
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 DEMO USER ACCOUNT:');
        $this->command->info('====================================');

        // Create or update user (idempotent)
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'password' => Hash::make($userData['password']),
                'account_type' => 'user',
                'is_admin' => false,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create or update profile
        if (!$user->profile) {
            $user->profile()->create($userData['profile']);
        } else {
            $user->profile->update($userData['profile']);
        }

        // Create or update address
        if (isset($userData['address'])) {
            $addressData = array_merge($userData['address'], [
                'type' => 'home',
                'is_primary' => true,
            ]);

            $existingAddress = $user->addresses()
                ->where('city', $userData['address']['city'])
                ->where('postal_code', $userData['address']['postal_code'])
                ->first();
                
            if ($existingAddress) {
                $existingAddress->update($addressData);
            } else {
                $user->addresses()->create($addressData);
            }
        }

        // Create emergency contact
        $contactName = 'Emergency Contact for Demo User';
        $existingContact = $user->emergencyContacts()->where('name', $contactName)->first();
        if (!$existingContact) {
            $user->emergencyContacts()->create([
                'name' => $contactName,
                'relationship' => 'friend',
                'phone' => '09123456789',
                'is_primary' => true,
            ]);
        }

        $this->command->line("📧 {$userData['email']} | 🔑 {$userData['password']} | 👤 {$userData['profile']['first_name']} {$userData['profile']['last_name']}");
        $this->command->info('✅ Demo user seeded successfully!');
    }
}
