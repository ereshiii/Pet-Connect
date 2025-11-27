<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class ClinicSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💳 Seeding clinic subscriptions...');

        // Get subscription plans
        $basicPlan = SubscriptionPlan::where('slug', 'basic-clinic')->first();
        $proPlan = SubscriptionPlan::where('slug', 'professional')->first();
        $proPlusPlan = SubscriptionPlan::where('slug', 'pro-plus')->first();

        if (!$basicPlan || !$proPlan || !$proPlusPlan) {
            $this->command->error('❌ Subscription plans not found. Please run SubscriptionPlanSeeder first.');
            return;
        }

        // Get all clinic users
        $clinicUsers = User::where('account_type', 'clinic')
            ->whereNotNull('clinic_registration_id')
            ->get();

        if ($clinicUsers->isEmpty()) {
            $this->command->error('❌ No clinic users found. Please run ClinicSeeder first.');
            return;
        }

        $subscriptionCounts = [
            'basic' => 0,
            'pro' => 0,
            'pro_plus' => 0,
        ];

        // Distribute subscriptions:
        // - 60% stay on Basic (free)
        // - 25% get Professional
        // - 15% get Pro Plus
        foreach ($clinicUsers as $index => $user) {
            $clinicName = $user->clinicRegistration->clinic_name ?? 'Unknown Clinic';
            
            // Determine subscription based on percentage distribution
            $random = rand(1, 100);
            
            if ($random <= 60) {
                // 60% - Keep Basic (free) - no subscription record needed as it's the default
                $subscriptionCounts['basic']++;
                $this->command->line("  📋 {$clinicName} - Basic (Free)");
                continue;
            } elseif ($random <= 85) {
                // 25% - Professional
                $plan = $proPlan;
                $planName = 'Professional';
                $subscriptionCounts['pro']++;
            } else {
                // 15% - Pro Plus
                $plan = $proPlusPlan;
                $planName = 'Pro Plus';
                $subscriptionCounts['pro_plus']++;
            }

            // Create subscription record
            $subscription = DB::table('subscriptions')->insert([
                'user_id' => $user->id,
                'type' => $plan->slug,
                'stripe_id' => 'sub_' . strtoupper(substr(md5($user->id . $plan->id . time()), 0, 24)),
                'stripe_status' => 'active',
                'stripe_price' => $plan->stripe_price_id,
                'quantity' => 1,
                'trial_ends_at' => null, // Already past trial
                'ends_at' => null, // Active subscription
                'created_at' => now()->subMonths(rand(1, 6)), // Subscribed 1-6 months ago
                'updated_at' => now(),
            ]);

            $this->command->line("  ✅ {$clinicName} - {$planName} (₱{$plan->price}/month)");
        }

        $this->command->info("\n📊 Subscription Distribution:");
        $this->command->line("  📋 Basic (Free): {$subscriptionCounts['basic']} clinics");
        $this->command->line("  💼 Professional: {$subscriptionCounts['pro']} clinics");
        $this->command->line("  ⭐ Pro Plus: {$subscriptionCounts['pro_plus']} clinics");
        $this->command->info("\n✅ Clinic subscriptions seeded successfully!");
    }
}
