<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate address data from user_profiles to user_addresses
        $profiles = DB::table('user_profiles')
            ->whereNotNull('address')
            ->orWhereNotNull('city')
            ->orWhereNotNull('province')
            ->get();

        foreach ($profiles as $profile) {
            // Check if user already has an address record
            $existingAddress = DB::table('user_addresses')
                ->where('user_id', $profile->user_id)
                ->where('is_primary', true)
                ->first();

            if (!$existingAddress && ($profile->address || $profile->city || $profile->province)) {
                DB::table('user_addresses')->insert([
                    'user_id' => $profile->user_id,
                    'type' => 'home',
                    'address_line_1' => $profile->address ?? '',
                    'address_line_2' => $profile->barangay ? 'Brgy. ' . $profile->barangay : null,
                    'city' => $profile->city ?? '',
                    'state' => $profile->province ?? '',
                    'postal_code' => $profile->zip_code,
                    'country' => 'Philippines',
                    'latitude' => $profile->latitude,
                    'longitude' => $profile->longitude,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Migrate emergency contact data from user_profiles to user_emergency_contacts
        $profilesWithEmergency = DB::table('user_profiles')
            ->whereNotNull('emergency_contact_name')
            ->orWhereNotNull('emergency_contact_phone')
            ->get();

        foreach ($profilesWithEmergency as $profile) {
            // Check if user already has an emergency contact record
            $existingContact = DB::table('user_emergency_contacts')
                ->where('user_id', $profile->user_id)
                ->where('is_primary', true)
                ->first();

            if (!$existingContact && ($profile->emergency_contact_name || $profile->emergency_contact_phone)) {
                DB::table('user_emergency_contacts')->insert([
                    'user_id' => $profile->user_id,
                    'name' => $profile->emergency_contact_name ?? '',
                    'relationship' => $profile->emergency_contact_relationship ?? 'other',
                    'phone' => $profile->emergency_contact_phone,
                    'email' => null, // emergency_contact_email field doesn't exist in user_profiles
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This doesn't restore data to user_profiles as that would cause data loss
        // The fields still exist in user_profiles for now
        // We'll remove them in a future migration after verification
    }
};
