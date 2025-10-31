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
            UserSeeder::class,
            AdminSeeder::class,
            ClinicSeeder::class,
        ]);
        
        $this->command->info('✅ Organized database seeding completed!');
        $this->command->info('');
        $this->command->info('📊 Account Summary:');
        $this->command->info('👥 Regular Users: 10 accounts');
        $this->command->info('🏥 Clinic Accounts: 5 accounts');
        $this->command->info('👑 Admin Account: 1 account');
        $this->command->info('');
        $this->command->info('🔐 Check the output above for login credentials!');
    }
}
