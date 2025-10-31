<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClinicRegistration;

class UpdateExistingClinicUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all clinic users that have old registration data
        $clinicUsers = User::where('account_type', 'clinic')
            ->whereNotNull('clinic_registration_data')
            ->get();

        foreach ($clinicUsers as $user) {
            // Check if they already have a new clinic registration record
            if (!$user->clinicRegistration) {
                $oldData = $user->clinic_registration_data ?? [];
                
                // Create new clinic registration record
                ClinicRegistration::create([
                    'user_id' => $user->id,
                    'clinic_name' => $oldData['clinic_name'] ?? 'Test Clinic',
                    'clinic_description' => $oldData['clinic_description'] ?? null,
                    'website' => $oldData['website'] ?? null,
                    'email' => $oldData['email'] ?? $user->email,
                    'phone' => $oldData['phone'] ?? '09123456789',
                    'country' => 'Philippines',
                    'region' => $oldData['region'] ?? 'National Capital Region',
                    'province' => $oldData['province'] ?? 'Metro Manila',
                    'city' => $oldData['city'] ?? 'Quezon City',
                    'barangay' => $oldData['barangay'] ?? 'Barangay 1',
                    'street_address' => $oldData['street_address'] ?? '123 Test Street',
                    'postal_code' => $oldData['postal_code'] ?? '1100',
                    'operating_hours' => $oldData['operating_hours'] ?? [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '08:00', 'close' => '12:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ],
                    'is_24_hours' => $oldData['is_24_hours'] ?? false,
                    'services' => $oldData['services'] ?? ['consultation', 'vaccination'],
                    'other_services' => $oldData['other_services'] ?? null,
                    'veterinarians' => $oldData['veterinarians'] ?? [
                        [
                            'name' => 'Dr. Test Veterinarian',
                            'license_number' => 'VET-12345',
                            'specialization' => 'General Practice'
                        ]
                    ],
                    'certification_proofs' => $oldData['certification_proofs'] ?? [],
                    'additional_info' => $oldData['additional_info'] ?? null,
                    'status' => $user->clinic_registration_status ?? 'incomplete',
                    'submitted_at' => $user->clinic_registration_submitted_at,
                    'approved_at' => $user->clinic_registration_approved_at,
                    'rejection_reason' => $user->clinic_rejection_reason,
                ]);
            }
        }

        // Also create clinic registrations for any clinic users without data
        $clinicUsersWithoutData = User::where('account_type', 'clinic')
            ->whereDoesntHave('clinicRegistration')
            ->get();

        foreach ($clinicUsersWithoutData as $user) {
            ClinicRegistration::create([
                'user_id' => $user->id,
                'status' => 'incomplete'
            ]);
        }

        $this->command->info('Successfully migrated ' . ($clinicUsers->count() + $clinicUsersWithoutData->count()) . ' clinic users to new table structure.');
    }
}
