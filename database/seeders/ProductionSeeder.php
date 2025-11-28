<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production data only.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting production database seeding...');
        
        // Seed reference data and demo data for production
        $this->call([
            // Step 1: Pet reference data
            PetTypeSeeder::class,
            PetBreedSeeder::class,
            
            // Step 2: Subscription plans
            SubscriptionPlanSeeder::class,
            
            // Step 3: Admin account
            AdminSeeder::class,
            
            // Step 4: Demo clinic accounts (for demonstration)
            ClinicSeeder::class,
            
            // Step 4.5: Assign subscriptions to clinics
            ClinicSubscriptionSeeder::class,
            
            // Step 5: Demo user with pets
            UserSeeder::class,
            PetSeeder::class,
            
            // Step 6: Demo appointments, medical records, and reviews
            AppointmentSeeder::class,
            MedicalRecordSeeder::class,
            ReviewSeeder::class,
        ]);
        
        $this->command->info('✅ Production database seeding completed!');
        $this->command->info('');
        $this->command->info('📊 Seeded Data:');
        $this->command->info('🐾 Pet Types: 9 types');
        $this->command->info('🐕 Pet Breeds: 40+ breeds across all species');
        $this->command->info('💳 Subscription Plans: 3 tiers (Basic, Professional, Pro Plus)');
        $this->command->info('👑 Admin Account: Check AdminSeeder for credentials');
        $this->command->info('🏥 Demo Clinics: Multiple sample clinics for demonstration');
        $this->command->info('💼 Clinic Subscriptions: Distributed across all subscription tiers');
        $this->command->info('👤 Demo User: demo@petconnect.com / password123');
        $this->command->info('🐶 Demo Pets: 5 pets (3 dogs, 2 cats) for demo user');
        $this->command->info('📅 Demo Appointments: 20-30 appointments across various clinics');
        $this->command->info('📋 Medical Records: Auto-generated for completed appointments');
        $this->command->info('⭐ Reviews: Auto-generated for ~70% of completed appointments');
        $this->command->info('');
        $this->command->warn('⚠️  Remember to change admin credentials in production!');
        $this->command->info('');
        $this->command->info('🎯 Demo Account Login:');
        $this->command->info('   Email: demo@petconnect.com');
        $this->command->info('   Password: password123');
    }
}
