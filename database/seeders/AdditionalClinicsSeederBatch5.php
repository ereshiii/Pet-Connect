<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClinicRegistration;
use Illuminate\Support\Facades\Hash;

class AdditionalClinicsSeederBatch5 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 Seeding additional clinic accounts - FINAL BATCH 5...');

        // BATCH 5: Final Regions (5 clinics) - Completing to 50 total
        $clinics = [
            [
                'email' => 'contact@leyte vetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Jovita',
                    'last_name' => 'Esperanza',
                    'phone' => '09221234551',
                    'date_of_birth' => '1979-03-17',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Historical province veterinarian with typhoon disaster response.',
                ],
                'clinic' => [
                    'clinic_name' => 'Leyte Veterinary Center',
                    'phone' => '053-234-5678',
                    'country' => 'Philippines',
                    'region' => 'Eastern Visayas',
                    'province' => 'Leyte',
                    'city' => 'Tacloban City',
                    'barangay' => 'Poblacion',
                    'street_address' => '789 Gen. MacArthur Highway',
                    'postal_code' => '6500',
                    'latitude' => 11.2439,
                    'longitude' => 125.0063,
                    'services' => ['consultation', 'vaccination', 'surgery', 'disaster response', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Jovita Esperanza',
                            'license_number' => 'VET-2024-050',
                            'specialization' => 'Disaster Response Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '18:00'],
                        'thursday' => ['open' => '08:00', 'close' => '18:00'],
                        'friday' => ['open' => '08:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ],
            [
                'email' => 'info@biliran petcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Segundino',
                    'last_name' => 'Villanueva',
                    'phone' => '09331234552',
                    'date_of_birth' => '1976-11-24',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Island province veterinarian with marine life expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Biliran Pet Care',
                    'phone' => '053-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Eastern Visayas',
                    'province' => 'Biliran',
                    'city' => 'Naval',
                    'barangay' => 'Poblacion',
                    'street_address' => '456 Provincial Road',
                    'postal_code' => '6550',
                    'latitude' => 11.5756,
                    'longitude' => 124.4681,
                    'services' => ['consultation', 'vaccination', 'surgery', 'marine care', 'aquatic'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Segundino Villanueva',
                            'license_number' => 'VET-2024-051',
                            'specialization' => 'Marine Life Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ],
            [
                'email' => 'admin@basilan vetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Hadji',
                    'last_name' => 'Abdulla',
                    'phone' => '09441234553',
                    'date_of_birth' => '1981-08-09',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Southern island veterinarian with halal veterinary practices.',
                ],
                'clinic' => [
                    'clinic_name' => 'Basilan Veterinary Clinic',
                    'phone' => '062-456-7890',
                    'country' => 'Philippines',
                    'region' => 'Bangsamoro Autonomous Region in Muslim Mindanao',
                    'province' => 'Basilan',
                    'city' => 'Isabela City',
                    'barangay' => 'Poblacion',
                    'street_address' => '123 Veterans Avenue',
                    'postal_code' => '7300',
                    'latitude' => 6.7042,
                    'longitude' => 121.9711,
                    'services' => ['consultation', 'vaccination', 'surgery', 'halal practice', 'island care'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Hadji Abdulla',
                            'license_number' => 'VET-2024-052',
                            'specialization' => 'Halal Veterinary Practice'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '14:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ],
            [
                'email' => 'contact@sulu animalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Fatima',
                    'last_name' => 'Jamalul',
                    'phone' => '09551234554',
                    'date_of_birth' => '1984-01-30',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Archipelago veterinarian with pearl diving animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Sulu Animal Hospital',
                    'phone' => '068-567-8901',
                    'country' => 'Philippines',
                    'region' => 'Bangsamoro Autonomous Region in Muslim Mindanao',
                    'province' => 'Sulu',
                    'city' => 'Jolo',
                    'barangay' => 'Poblacion',
                    'street_address' => '987 Pier Road',
                    'postal_code' => '7400',
                    'latitude' => 6.0538,
                    'longitude' => 121.0075,
                    'services' => ['consultation', 'vaccination', 'surgery', 'pearl diving', 'archipelago'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Fatima Jamalul',
                            'license_number' => 'VET-2024-053',
                            'specialization' => 'Archipelago Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '14:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ],
            [
                'email' => 'info@tawi-tawi petcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Almahdi',
                    'last_name' => 'Sahidulla',
                    'phone' => '09661234555',
                    'date_of_birth' => '1973-12-05',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Southernmost veterinarian with border area animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Tawi-Tawi Pet Center',
                    'phone' => '068-678-9012',
                    'country' => 'Philippines',
                    'region' => 'Bangsamoro Autonomous Region in Muslim Mindanao',
                    'province' => 'Tawi-Tawi',
                    'city' => 'Bongao',
                    'barangay' => 'Poblacion',
                    'street_address' => '654 Border Highway',
                    'postal_code' => '7500',
                    'latitude' => 5.0319,
                    'longitude' => 119.7744,
                    'services' => ['consultation', 'vaccination', 'surgery', 'border care', 'southernmost'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Almahdi Sahidulla',
                            'license_number' => 'VET-2024-054',
                            'specialization' => 'Border Area Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '14:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 FINAL BATCH 5 - CLINIC ACCOUNTS:');
        $this->command->info('====================================');

        foreach ($clinics as $clinicData) {
            // Create clinic user account
            $user = User::create([
                'email' => $clinicData['email'],
                'password' => Hash::make($clinicData['password']),
                'account_type' => 'clinic',
                'is_admin' => false,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            // Create user profile for the clinic owner/admin
            $user->profile()->create($clinicData['profile']);

            // Create clinic registration
            $registration = ClinicRegistration::create(array_merge($clinicData['clinic'], [
                'user_id' => $user->id,
                'email' => $clinicData['email'],
                'certification_proofs' => [],
                'status' => 'approved',
                'submitted_at' => now(),
                'approved_at' => now(),
                'approved_by' => 1, // Admin user ID
            ]));

            $this->command->line("📧 {$clinicData['email']} | 🔑 {$clinicData['password']} | 🏥 {$clinicData['clinic']['clinic_name']}");
        }

        $this->command->info('🎉 FINAL Batch 5 (5 clinics) seeded successfully!');
        $this->command->info('🏆 MISSION COMPLETE: 50 total clinics (5 original + 45 additional)');
        $this->command->info('🌏 Geographic Coverage: ALL MAJOR PHILIPPINE REGIONS COMPLETED!');
    }
}