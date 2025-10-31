<?php

namespace Database\Seeders;

use App\Models\ClinicService;
use App\Models\ClinicRegistration;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClinicServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a clinic user
        $clinicUser = User::where('account_type', 'clinic')->first();
        
        if (!$clinicUser) {
            echo "No clinic user found. Please run ClinicUserSeeder first.\n";
            return;
        }

        // Get or create a clinic registration for this user
        $clinicRegistration = ClinicRegistration::where('user_id', $clinicUser->id)->first();
        
        if (!$clinicRegistration) {
            $clinicRegistration = ClinicRegistration::create([
                'user_id' => $clinicUser->id,
                'clinic_name' => 'PetCare Veterinary Clinic',
                'clinic_description' => 'Comprehensive veterinary services for all your pet needs.',
                'email' => $clinicUser->email,
                'phone' => '+63 912 345 6789',
                'country' => 'Philippines',
                'region' => 'National Capital Region',
                'province' => 'Metro Manila',
                'city' => 'Quezon City',
                'barangay' => 'Barangay Santo Domingo',
                'street_address' => '123 Pet Street',
                'postal_code' => '1100',
                'latitude' => 14.6760,
                'longitude' => 121.0437,
                'operating_hours' => [
                    'monday' => ['open' => '08:00', 'close' => '18:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '18:00'],
                    'wednesday' => ['open' => '08:00', 'close' => '18:00'],
                    'thursday' => ['open' => '08:00', 'close' => '18:00'],
                    'friday' => ['open' => '08:00', 'close' => '18:00'],
                    'saturday' => ['open' => '08:00', 'close' => '16:00'],
                    'sunday' => ['open' => '09:00', 'close' => '15:00'],
                ],
                'services' => ['consultation', 'vaccination', 'surgery', 'dental'],
                'veterinarians' => [
                    ['name' => 'Dr. Maria Santos', 'license' => 'VET-2023-001'],
                    ['name' => 'Dr. John Cruz', 'license' => 'VET-2023-002'],
                ],
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        }

        // Create sample services
        $services = [
            [
                'name' => 'General Consultation',
                'description' => 'Comprehensive health examination and consultation for your pet',
                'category' => 'consultation',
                'base_price' => 500.00,
                'duration_minutes' => 30,
                'is_active' => true,
                'requires_appointment' => true,
                'is_emergency_service' => false,
            ],
            [
                'name' => 'Vaccination Package',
                'description' => 'Complete vaccination schedule for puppies and adult dogs',
                'category' => 'vaccination',
                'base_price' => 1200.00,
                'duration_minutes' => 20,
                'is_active' => true,
                'requires_appointment' => true,
                'is_emergency_service' => false,
            ],
            [
                'name' => 'Dental Cleaning',
                'description' => 'Professional dental cleaning and oral health assessment',
                'category' => 'dental',
                'base_price' => 2500.00,
                'duration_minutes' => 60,
                'is_active' => true,
                'requires_appointment' => true,
                'is_emergency_service' => false,
            ],
            [
                'name' => 'Spay/Neuter Surgery',
                'description' => 'Safe spaying or neutering procedure with post-op care',
                'category' => 'surgery',
                'base_price' => 5000.00,
                'duration_minutes' => 120,
                'is_active' => true,
                'requires_appointment' => true,
                'is_emergency_service' => false,
            ],
            [
                'name' => 'Emergency Care',
                'description' => '24/7 emergency veterinary care for critical cases',
                'category' => 'emergency',
                'base_price' => 3000.00,
                'duration_minutes' => 45,
                'is_active' => true,
                'requires_appointment' => false,
                'is_emergency_service' => true,
            ],
            [
                'name' => 'Pet Grooming',
                'description' => 'Full grooming service including bath, nail trim, and styling',
                'category' => 'grooming',
                'base_price' => 800.00,
                'duration_minutes' => 90,
                'is_active' => true,
                'requires_appointment' => true,
                'is_emergency_service' => false,
            ],
            [
                'name' => 'Laboratory Tests',
                'description' => 'Blood work, urinalysis, and other diagnostic tests',
                'category' => 'diagnostic',
                'base_price' => 1500.00,
                'duration_minutes' => 30,
                'is_active' => true,
                'requires_appointment' => true,
                'is_emergency_service' => false,
            ],
            [
                'name' => 'X-Ray Imaging',
                'description' => 'Digital radiography for accurate diagnosis',
                'category' => 'diagnostic',
                'base_price' => 2000.00,
                'duration_minutes' => 20,
                'is_active' => true,
                'requires_appointment' => true,
                'is_emergency_service' => false,
            ],
        ];

        foreach ($services as $serviceData) {
            ClinicService::create(array_merge($serviceData, [
                'clinic_id' => $clinicRegistration->id,
            ]));
        }

        echo "Created " . count($services) . " services for clinic: " . $clinicRegistration->clinic_name . "\n";
    }
}