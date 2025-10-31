<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing user profile data to new user_profiles table
        $this->migrateUserProfiles();
        
        // Migrate existing clinic registration data to new clinics table
        $this->migrateClinicData();
        
        // Create default system settings
        $this->createDefaultSystemSettings();
    }

    private function migrateUserProfiles(): void
    {
        // Get all existing users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Extract first/last name from name field if it exists
            $nameParts = explode(' ', $user->name ?? '', 2);
            $firstName = $nameParts[0] ?? null;
            $lastName = $nameParts[1] ?? null;
            
            // Create user profile entry
            DB::table('user_profiles')->insert([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => null, // Not available in current structure
                'phone' => $user->phone ?? null,
                'date_of_birth' => $user->date_of_birth ?? null,
                'gender' => $user->gender ?? null,
                'occupation' => null, // Not available in current structure
                'bio' => $user->bio ?? null,
                'profile_image' => null, // Not available in current structure
                'preferred_language' => 'en',
                'timezone' => 'Asia/Manila',
                'emergency_contact_name' => $user->emergency_contact_name ?? null,
                'emergency_contact_phone' => $user->emergency_contact_phone ?? null,
                'emergency_contact_relationship' => $user->emergency_contact_relationship ?? null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

            // Create user address if location data exists
            if ($user->address_line_1 ?? $user->city ?? $user->state) {
                DB::table('user_addresses')->insert([
                    'user_id' => $user->id,
                    'type' => 'home',
                    'address_line_1' => $user->address_line_1 ?? '',
                    'address_line_2' => $user->address_line_2 ?? '',
                    'city' => $user->city ?? '',
                    'state' => $user->state ?? '',
                    'postal_code' => $user->postal_code ?? '',
                    'country' => $user->country ?? 'Philippines',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create emergency contact entry if exists
            if ($user->emergency_contact_name) {
                DB::table('user_emergency_contacts')->insert([
                    'user_id' => $user->id,
                    'name' => $user->emergency_contact_name,
                    'phone' => $user->emergency_contact_phone ?? '',
                    'relationship' => $user->emergency_contact_relationship ?? 'other',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function migrateClinicData(): void
    {
        // Get all existing clinic registrations
        $clinicRegistrations = DB::table('clinic_registrations')->get();
        
        foreach ($clinicRegistrations as $registration) {
            // Create clinic entry
            $clinicId = DB::table('clinics')->insertGetId([
                'registration_id' => $registration->id,
                'name' => $registration->clinic_name ?: 'Clinic #' . $registration->id,
                'license_number' => 'TEMP-' . $registration->id, // Generate temporary license number
                'type' => 'general', // Default type
                'description' => $registration->clinic_description ?? $registration->services ?? null,
                'phone' => $registration->phone ?? '',
                'email' => $registration->email ?? '',
                'website' => $registration->website ?? null,
                'status' => $registration->status === 'approved' ? 'active' : 'inactive',
                'created_at' => $registration->created_at,
                'updated_at' => $registration->updated_at,
            ]);

            // Create clinic address if location data exists
            if ($registration->street_address || $registration->city) {
                DB::table('clinic_addresses')->insert([
                    'clinic_id' => $clinicId,
                    'type' => 'main',
                    'address_line_1' => $registration->street_address ?? '',
                    'address_line_2' => $registration->barangay ?? '',
                    'city' => $registration->city ?? '',
                    'state' => $registration->province ?? '',
                    'postal_code' => $registration->postal_code ?? '',
                    'country' => $registration->country ?? 'Philippines',
                    'latitude' => $registration->latitude ?? null,
                    'longitude' => $registration->longitude ?? null,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create default operating hours (Monday-Friday 9AM-5PM) if not 24/7
            if (!$registration->is_24_hours && !$registration->is_open_24_7) {
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                foreach ($days as $day) {
                    DB::table('clinic_operating_hours')->insert([
                        'clinic_id' => $clinicId,
                        'day_of_week' => $day,
                        'opening_time' => '09:00:00',
                        'closing_time' => '17:00:00',
                        'is_closed' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Weekend hours (Saturday half day, Sunday closed)
                DB::table('clinic_operating_hours')->insert([
                    'clinic_id' => $clinicId,
                    'day_of_week' => 'saturday',
                    'opening_time' => '09:00:00',
                    'closing_time' => '12:00:00',
                    'is_closed' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('clinic_operating_hours')->insert([
                    'clinic_id' => $clinicId,
                    'day_of_week' => 'sunday',
                    'opening_time' => null,
                    'closing_time' => null,
                    'is_closed' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // 24/7 operating hours
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                foreach ($days as $day) {
                    DB::table('clinic_operating_hours')->insert([
                        'clinic_id' => $clinicId,
                        'day_of_week' => $day,
                        'opening_time' => '00:00:00',
                        'closing_time' => '23:59:59',
                        'is_closed' => false,
                        'notes' => '24/7 Operation',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function createDefaultSystemSettings(): void
    {
        $defaultSettings = [
            [
                'key' => 'app_name',
                'value' => 'PetConnect',
                'type' => 'string',
                'description' => 'Application name',
                'group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'appointment_duration_default',
                'value' => '30',
                'type' => 'int',
                'description' => 'Default appointment duration in minutes',
                'group' => 'appointments',
                'is_public' => false,
            ],
            [
                'key' => 'reminder_hours_before',
                'value' => '24',
                'type' => 'int',
                'description' => 'Hours before appointment to send reminder',
                'group' => 'notifications',
                'is_public' => false,
            ],
            [
                'key' => 'max_pets_per_user',
                'value' => '10',
                'type' => 'int',
                'description' => 'Maximum number of pets per user',
                'group' => 'limits',
                'is_public' => false,
            ],
            [
                'key' => 'clinic_approval_required',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Whether clinic registrations require admin approval',
                'group' => 'clinic_management',
                'is_public' => false,
            ],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('system_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear migrated data (optional - be careful with this in production)
        DB::table('user_emergency_contacts')->truncate();
        DB::table('user_addresses')->truncate();
        DB::table('user_profiles')->truncate();
        DB::table('clinic_operating_hours')->truncate();
        DB::table('clinic_addresses')->truncate();
        DB::table('clinics')->truncate();
        DB::table('system_settings')->truncate();
    }
};
