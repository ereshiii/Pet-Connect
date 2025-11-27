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
        // Clinic Plans (Pet owners have no subscriptions as per requirements)

        \App\Models\SubscriptionPlan::updateOrCreate(
            ['slug' => 'basic-clinic'],
            [
                'name' => 'Basic Clinic',
                'type' => 'clinic',
                'stripe_price_id' => 'basic-clinic',  // Use slug as identifier
                'description' => 'Free forever plan for small veterinary practices',
                'price' => 0.00,
                'annual_price' => 0.00,
                'features' => [
                    'dashboard',
                    'appointments',
                    'schedule_management',
                ],
                'limits' => [
                    'max_appointments_per_month' => -1, // unlimited
                    'max_staff_accounts' => 1,
                    'max_services' => 3,
                    'max_locations' => 1,
                    'storage_mb' => 500,
                    'reports' => false,
                    'analytics' => false,
                    'history' => false,
                    'patient_records' => false,
                ],
                'is_active' => true,
                'trial_days' => 0, // No trial needed - free forever
                'transaction_fee_percentage' => 0.00, // No transaction fee for free plan
                'transaction_fee_fixed' => 0.00,
                'sort_order' => 1,
            ]
        );

        \App\Models\SubscriptionPlan::updateOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional',
                'type' => 'clinic',
                'stripe_price_id' => 'professional',  // Use slug as identifier
                'description' => 'Enhanced features for growing veterinary practices',
                'price' => 599.00, // ₱599/month
                'annual_price' => 5990.00, // ₱5,990/year (save ₱1,198 - 17% discount)
                'features' => [
                    'dashboard',
                    'appointments',
                    'schedule_management',
                    'analytics',
                    'history',
                    'patient_records',
                ],
                'limits' => [
                    'max_appointments_per_month' => -1, // unlimited
                    'max_staff_accounts' => 3,
                    'max_services' => 10,
                    'max_locations' => 2,
                    'storage_mb' => 3000,
                    'reports' => false,
                    'analytics' => true,
                    'history' => true,
                    'patient_records' => true,
                ],
                'is_active' => true,
                'trial_days' => 14,
                'transaction_fee_percentage' => 3.50, // 3.5% transaction fee
                'transaction_fee_fixed' => 0.00,
                'sort_order' => 2,
            ]
        );

        \App\Models\SubscriptionPlan::updateOrCreate(
            ['slug' => 'pro-plus'],
            [
                'name' => 'Pro Plus',
                'type' => 'clinic',
                'stripe_price_id' => 'pro-plus',  // Use slug as identifier
                'description' => 'Advanced features for professional veterinary practices',
                'price' => 1499.00, // ₱1,499/month
                'annual_price' => 14990.00, // ₱14,990/year (save ₱3,498 - 20% discount)
            'features' => [
                'dashboard',
                'appointments',
                'schedule_management',
                'analytics',
                'history',
                'patient_records',
                'report_generation',
            ],
            'limits' => [
                'max_appointments_per_month' => -1, // unlimited
                'max_staff_accounts' => -1, // unlimited
                'max_services' => -1, // unlimited
                'max_locations' => 5,
                'storage_mb' => 20000,
                'reports' => true,
                'analytics' => true,
                'history' => true,
                'patient_records' => true,
            ],
            'is_active' => true,
            'trial_days' => 30,
            'transaction_fee_percentage' => 2.50, // 2.5% transaction fee
            'transaction_fee_fixed' => 0.00,
            'sort_order' => 3,
        ]
        );
    }
}
