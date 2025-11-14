<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👑 Seeding admin account...');

        // Create or update admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@petconnect.ph'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'account_type' => 'admin',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create admin profile if it doesn't exist
        if (!$admin->profile) {
            $admin->profile()->create([
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'phone' => '09171234567',
                'date_of_birth' => '1985-01-01',
                'gender' => 'prefer_not_to_say',
                'occupation' => 'System Administrator',
                'bio' => 'PetConnect Platform Administrator - Managing the pet healthcare platform for the Philippines.',
                'preferred_language' => 'en',
                'timezone' => 'Asia/Manila',
            ]);
        }

        // Create admin address if it doesn't exist
        if ($admin->addresses()->count() === 0) {
            $admin->addresses()->create([
                'type' => 'work',
                'address_line_1' => 'PetConnect HQ, BGC',
                'address_line_2' => '26th Floor, High Street South Tower',
                'city' => 'Taguig',
                'state' => 'Metro Manila',
                'postal_code' => '1634',
                'country' => 'Philippines',
                'latitude' => 14.5514,
                'longitude' => 121.0496,
                'is_primary' => true,
            ]);
        }

        // Create admin emergency contact if it doesn't exist
        if ($admin->emergencyContacts()->count() === 0) {
            $admin->emergencyContacts()->create([
                'name' => 'IT Support Team',
                'relationship' => 'other',
                'phone' => '09171234568',
                'email' => 'support@petconnect.ph',
                'is_primary' => true,
            ]);
        }

        $this->command->info('');
        $this->command->info('🔐 ADMIN ACCOUNT:');
        $this->command->info('====================================');
        $this->command->line('📧 admin@petconnect.ph | 🔑 admin123 | 👤 System Administrator');
        $this->command->info('✅ Admin account seeded successfully!');
    }
}
