<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pet Owner Plans
        \App\Models\SubscriptionPlan::create([
            'name' => 'Free Pet Owner',
            'slug' => 'free-pet-owner',
            'type' => 'user',
            'description' => 'Basic pet care management for casual pet owners',
            'price' => 0.00,
            'annual_price' => 0.00,
            'features' => [
                'basic_pet_profiles',
                'standard_appointment_booking',
                'basic_health_records',
                'community_access',
            ],
            'limits' => [
                'max_pets' => 2,
                'max_appointments_per_month' => 10,
                'storage_mb' => 100,
            ],
            'is_active' => true,
            'trial_days' => 0,
            'sort_order' => 1,
        ]);

        \App\Models\SubscriptionPlan::create([
            'name' => 'Premium Pet Owner',
            'slug' => 'premium-pet-owner',
            'type' => 'user',
            'description' => 'Advanced pet care with unlimited pets and premium features',
            'price' => 199.00, // ₱199/month
            'annual_price' => 1990.00, // ₱1,990/year (save ₱398)
            'features' => [
                'unlimited_pets',
                'advanced_health_tracking',
                'priority_booking',
                'telemedicine',
                'health_reports',
                'export_records',
                'vaccination_reminders',
                'medical_history_timeline',
                'emergency_contact_alerts',
            ],
            'limits' => [
                'max_pets' => -1, // unlimited
                'max_appointments_per_month' => -1, // unlimited
                'storage_mb' => 1000,
            ],
            'is_active' => true,
            'trial_days' => 14,
            'sort_order' => 2,
        ]);

        // Clinic Plans
        \App\Models\SubscriptionPlan::create([
            'name' => 'Basic Clinic',
            'slug' => 'basic-clinic',
            'type' => 'clinic',
            'description' => 'Essential features for small veterinary practices',
            'price' => 0.00,
            'annual_price' => 0.00,
            'features' => [
                'profile_listing',
                'basic_calendar',
                'standard_reviews',
                'basic_patient_management',
            ],
            'limits' => [
                'max_appointments_per_month' => 50,
                'max_staff_accounts' => 1,
                'max_locations' => 1,
                'storage_mb' => 500,
            ],
            'is_active' => true,
            'trial_days' => 0,
            'transaction_fee_percentage' => 5.00, // 5% transaction fee
            'transaction_fee_fixed' => 0.00,
            'sort_order' => 1,
        ]);

        \App\Models\SubscriptionPlan::create([
            'name' => 'Professional Clinic',
            'slug' => 'professional-clinic',
            'type' => 'clinic',
            'description' => 'Advanced features for growing veterinary practices',
            'price' => 1499.00, // ₱1,499/month
            'annual_price' => 14990.00, // ₱14,990/year (save ₱3,498)
            'features' => [
                'unlimited_appointments',
                'advanced_scheduling',
                'staff_management',
                'detailed_analytics',
                'custom_forms',
                'priority_listing',
                'inventory_management',
                'automated_reminders',
                'financial_reporting',
            ],
            'limits' => [
                'max_appointments_per_month' => -1, // unlimited
                'max_staff_accounts' => 5,
                'max_locations' => 1,
                'storage_mb' => 5000,
            ],
            'is_active' => true,
            'trial_days' => 30,
            'transaction_fee_percentage' => 2.50, // 2.5% transaction fee
            'transaction_fee_fixed' => 0.00,
            'sort_order' => 2,
        ]);

        \App\Models\SubscriptionPlan::create([
            'name' => 'Enterprise Clinic',
            'slug' => 'enterprise-clinic',
            'type' => 'clinic',
            'description' => 'Complete solution for large veterinary chains and hospitals',
            'price' => 4999.00, // ₱4,999/month
            'annual_price' => 49990.00, // ₱49,990/year (save ₱9,998)
            'features' => [
                'multi_location',
                'unlimited_staff',
                'api_access',
                'white_label_app',
                'advanced_reporting',
                'dedicated_support',
                'custom_integrations',
                'advanced_analytics',
                'compliance_tools',
                'multi_currency',
            ],
            'limits' => [
                'max_appointments_per_month' => -1, // unlimited
                'max_staff_accounts' => -1, // unlimited
                'max_locations' => -1, // unlimited
                'storage_mb' => 50000,
            ],
            'is_active' => true,
            'trial_days' => 30,
            'transaction_fee_percentage' => 1.00, // 1% transaction fee
            'transaction_fee_fixed' => 0.00,
            'sort_order' => 3,
        ]);
    }
}
