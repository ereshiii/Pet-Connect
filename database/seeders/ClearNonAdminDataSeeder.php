<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearNonAdminDataSeeder extends Seeder
{
    /**
     * Clear all database data except admin users.
     * Preserves table structures and admin user data.
     */
    public function run(): void
    {
        // Disable foreign key constraints temporarily
        DB::statement('PRAGMA foreign_keys = OFF');

        // Get admin user IDs to preserve
        $adminIds = DB::table('users')
            ->where('account_type', 'admin')
            ->pluck('id')
            ->toArray();

        $this->command->info('Admin users to preserve: ' . implode(', ', $adminIds));

        // Delete in order respecting foreign key dependencies

        // 1. Appointment system (deepest dependencies first)
        $this->command->info('Clearing appointment data...');
        if (Schema::hasTable('appointment_follow_ups')) {
            DB::table('appointment_follow_ups')->delete();
        }
        if (Schema::hasTable('appointment_reminders')) {
            DB::table('appointment_reminders')->delete();
        }
        if (Schema::hasTable('appointment_waiting_list')) {
            DB::table('appointment_waiting_list')->delete();
        }
        if (Schema::hasTable('appointments')) {
            DB::table('appointments')->delete();
        }
        if (Schema::hasTable('appointment_time_slots')) {
            DB::table('appointment_time_slots')->delete();
        }

        // 2. Pet-related tables
        $this->command->info('Clearing pet data...');
        if (Schema::hasTable('pet_health_conditions')) {
            DB::table('pet_health_conditions')->delete();
        }
        if (Schema::hasTable('pet_vaccinations')) {
            DB::table('pet_vaccinations')->delete();
        }
        if (Schema::hasTable('pet_medical_records')) {
            DB::table('pet_medical_records')->delete();
        }
        if (Schema::hasTable('pets')) {
            DB::table('pets')->delete();
        }

        // 3. Clinic reviews and staff
        $this->command->info('Clearing clinic operational data...');
        if (Schema::hasTable('clinic_reviews')) {
            DB::table('clinic_reviews')->delete();
        }
        if (Schema::hasTable('clinic_staff')) {
            DB::table('clinic_staff')->delete();
        }
        if (Schema::hasTable('clinic_equipment')) {
            DB::table('clinic_equipment')->delete();
        }
        if (Schema::hasTable('clinic_operating_hours')) {
            DB::table('clinic_operating_hours')->delete();
        }
        if (Schema::hasTable('clinic_services')) {
            DB::table('clinic_services')->delete();
        }
        if (Schema::hasTable('clinic_addresses')) {
            DB::table('clinic_addresses')->delete();
        }

        // 4. Payment and subscription system
        $this->command->info('Clearing payment data...');
        if (Schema::hasTable('payments')) {
            DB::table('payments')->delete();
        }
        if (Schema::hasTable('invoice_items')) {
            DB::table('invoice_items')->delete();
        }
        if (Schema::hasTable('invoices')) {
            DB::table('invoices')->delete();
        }
        if (Schema::hasTable('subscription_items')) {
            DB::table('subscription_items')->delete();
        }
        if (Schema::hasTable('subscriptions')) {
            DB::table('subscriptions')->delete();
        }

        // 5. Clinic vets and favorites
        $this->command->info('Clearing clinic relationships...');
        if (Schema::hasTable('clinic_vets')) {
            DB::table('clinic_vets')->delete();
        }
        if (Schema::hasTable('user_clinic_favorites')) {
            DB::table('user_clinic_favorites')->delete();
        }
        if (Schema::hasTable('patient_edit_logs')) {
            DB::table('patient_edit_logs')->delete();
        }

        // 6. Clinics themselves (if legacy `clinics` table exists)
        $this->command->info('Clearing clinics (if present)...');
        if (\Schema::hasTable('clinics')) {
            DB::table('clinics')->delete();
        }

        // 7. Clinic registrations (except admin-owned)
        if (Schema::hasTable('clinic_registrations')) {
            DB::table('clinic_registrations')
                ->whereNotIn('user_id', $adminIds)
                ->delete();
        }

        // 8. User-related data (except admin)
        $this->command->info('Clearing user data (preserving admins)...');
        if (Schema::hasTable('user_profiles')) {
            DB::table('user_profiles')
                ->whereNotIn('user_id', $adminIds)
                ->delete();
        }

        if (Schema::hasTable('user_addresses')) {
            DB::table('user_addresses')
                ->whereNotIn('user_id', $adminIds)
                ->delete();
        }

        if (Schema::hasTable('user_emergency_contacts')) {
            DB::table('user_emergency_contacts')
                ->whereNotIn('user_id', $adminIds)
                ->delete();
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->whereNotIn('user_id', $adminIds)
                ->delete();
        }

        if (Schema::hasTable('analytics_events')) {
            DB::table('analytics_events')
                ->whereNotIn('user_id', $adminIds)
                ->delete();
        }

        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')
                ->whereNotIn('user_id', $adminIds)
                ->delete();
        }

        // 9. Support tickets (except admin)
        if (Schema::hasTable('support_ticket_responses')) {
            DB::table('support_ticket_responses')->delete();
        }
        if (Schema::hasTable('support_tickets')) {
            DB::table('support_tickets')
                ->whereNotIn('user_id', $adminIds)
                ->delete();
        }

        // 10. Finally, delete non-admin users
        $deletedUsers = 0;
        if (Schema::hasTable('users')) {
            $deletedUsers = DB::table('users')
                ->whereNotIn('id', $adminIds)
                ->where('account_type', '!=', 'admin')
                ->delete();
        }

        // Re-enable foreign key constraints
        DB::statement('PRAGMA foreign_keys = ON');

        $remainingUsers = DB::table('users')->count();

        $this->command->info("✅ Data clearing complete!");
        $this->command->info("Deleted {$deletedUsers} non-admin users");
        $this->command->info("Remaining users: {$remainingUsers} (admin only)");
    }
}
