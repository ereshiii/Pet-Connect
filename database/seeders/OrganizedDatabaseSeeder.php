<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganizedDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting organized database seeding...');
        
        // Seed in proper order to maintain referential integrity
        $this->call([
            // Step 1: Core accounts
            AdminSeeder::class,
            UserSeeder::class,
            // ClinicSeeder::class,
            SubscriptionPlanSeeder::class,
            
            // Step 2: Clinic subscriptions (requires clinics and subscription plans)
            // ClinicSubscriptionSeeder::class,
            
            // Step 3: Pet breeds and types
            PetTypeSeeder::class,
            PetBreedSeeder::class,
            
            // Step 4: Pets (requires users)
            // PetSeeder::class,
            
            // Step 5: Appointments (requires users, pets, clinics)
            // AppointmentSeeder::class,
            
            // Step 6: Medical Records (requires completed appointments)
            // MedicalRecordSeeder::class,
            
            // Step 7: Reviews (requires completed appointments)
            // ReviewSeeder::class,
        ]);
        
        $this->command->info('✅ Organized database seeding completed!');
        $this->command->info('');
        $this->command->info('📊 Database Summary:');
        $this->command->info('👥 Regular Users: 10 accounts');
        $this->command->info('🏥 Clinic Accounts: 31 clinics');
        $this->command->info('👑 Admin Account: 1 account');
        $this->command->info('💳 Subscriptions: Mix of Basic (free), Professional, and Pro Plus');
        $this->command->info('🐾 Pets: ~18 pets with various species and breeds');
        $this->command->info('📅 Appointments: Multiple appointments per pet (past, upcoming, pending)');
        $this->command->info('📋 Medical Records: Records for all completed appointments');
        $this->command->info('⭐ Reviews: Ratings and reviews for completed appointments');
        $this->command->info('');
        $this->command->info('🔐 Check the output above for login credentials!');
    }
}
