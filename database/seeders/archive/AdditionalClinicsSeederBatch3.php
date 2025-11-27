<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClinicRegistration;
use Illuminate\Support\Facades\Hash;

class AdditionalClinicsSeederBatch3 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 Seeding additional clinic accounts - BATCH 3...');

        // BATCH 3: Metro & Nearby Areas (10 clinics)
        $clinics = [
            [
                'email' => 'contact@quezoncityvet.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Isabel',
                    'last_name' => 'Reyes',
                    'phone' => '09191234531',
                    'date_of_birth' => '1984-02-20',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Metro veterinarian with multi-specialty clinic operations experience.',
                ],
                'clinic' => [
                    'clinic_name' => 'Quezon City Veterinary Clinic',
                    'phone' => '02-234-5678',
                    'country' => 'Philippines',
                    'region' => 'NCR',
                    'province' => 'Metro Manila',
                    'city' => 'Quezon City',
                    'barangay' => 'Project 4',
                    'street_address' => '12 Nayong Kanluran',
                    'postal_code' => '1112',
                    'latitude' => 14.6760,
                    'longitude' => 121.0437,
                    'services' => ['consultation', 'vaccination', 'surgery', 'imaging', 'boarding'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Isabel Reyes',
                            'license_number' => 'VET-2024-030',
                            'specialization' => 'Multi-specialty'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '21:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '21:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '21:00'],
                        'thursday' => ['open' => '08:00', 'close' => '21:00'],
                        'friday' => ['open' => '08:00', 'close' => '21:00'],
                        'saturday' => ['open' => '09:00', 'close' => '19:00'],
                        'sunday' => ['open' => '09:00', 'close' => '17:00']
                    ]
                ]
            ],
            [
                'email' => 'info@mandauevetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Ramon',
                    'last_name' => 'Cruz',
                    'phone' => '09291234532',
                    'date_of_birth' => '1979-06-02',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Cebu region veterinarian with urban clinic experience.',
                ],
                'clinic' => [
                    'clinic_name' => 'Mandaue Veterinary Clinic',
                    'phone' => '032-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Central Visayas',
                    'province' => 'Cebu',
                    'city' => 'Mandaue',
                    'barangay' => 'Guizo',
                    'street_address' => '86 A. Soriano Avenue',
                    'postal_code' => '6014',
                    'latitude' => 10.3319,
                    'longitude' => 123.9452,
                    'services' => ['consultation', 'vaccination', 'surgery', 'boarding', 'pharmacy'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Ramon Cruz',
                            'license_number' => 'VET-2024-031',
                            'specialization' => 'Urban Clinic'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '20:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '20:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '20:00'],
                        'thursday' => ['open' => '07:00', 'close' => '20:00'],
                        'friday' => ['open' => '07:00', 'close' => '20:00'],
                        'saturday' => ['open' => '08:00', 'close' => '18:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'contact@iloilovetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Monica',
                    'last_name' => 'Santos',
                    'phone' => '09391234533',
                    'date_of_birth' => '1986-09-09',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Western Visayas veterinarian with clinic and community programs.',
                ],
                'clinic' => [
                    'clinic_name' => 'Iloilo Vet Clinic',
                    'phone' => '033-456-7890',
                    'country' => 'Philippines',
                    'region' => 'Western Visayas',
                    'province' => 'Iloilo',
                    'city' => 'Iloilo City',
                    'barangay' => 'Lapuz',
                    'street_address' => '23 Ledesma Street',
                    'postal_code' => '5000',
                    'latitude' => 10.7183,
                    'longitude' => 122.5621,
                    'services' => ['consultation', 'vaccination', 'surgery', 'community outreach'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Monica Santos',
                            'license_number' => 'VET-2024-032',
                            'specialization' => 'Community Outreach'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '18:00'],
                        'thursday' => ['open' => '08:00', 'close' => '18:00'],
                        'friday' => ['open' => '08:00', 'close' => '18:00'],
                        'saturday' => ['open' => '09:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '14:00']
                    ]
                ]
            ],
            [
                'email' => 'info@bacolodpetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Ernesto',
                    'last_name' => 'Garcia',
                    'phone' => '09401234534',
                    'date_of_birth' => '1972-12-30',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Bacolod vet with community immunization program experience.',
                ],
                'clinic' => [
                    'clinic_name' => 'Bacolod Pet Clinic',
                    'phone' => '034-567-8901',
                    'country' => 'Philippines',
                    'region' => 'Western Visayas',
                    'province' => 'Negros Occidental',
                    'city' => 'Bacolod',
                    'barangay' => 'Villamonte',
                    'street_address' => '58 Lacson Street',
                    'postal_code' => '6100',
                    'latitude' => 10.6769,
                    'longitude' => 122.9446,
                    'services' => ['consultation', 'vaccination', 'community', 'spay/neuter'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Ernesto Garcia',
                            'license_number' => 'VET-2024-033',
                            'specialization' => 'Community Immunization'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '09:00', 'close' => '16:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'admin@roxaspetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Cesar',
                    'last_name' => 'Velasquez',
                    'phone' => '09511234535',
                    'date_of_birth' => '1970-03-13',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Panay Island veterinarian with a focus on small animal surgery.',
                ],
                'clinic' => [
                    'clinic_name' => 'Roxas Pet Clinic',
                    'phone' => '036-678-9012',
                    'country' => 'Philippines',
                    'region' => 'Western Visayas',
                    'province' => 'Capiz',
                    'city' => 'Roxas City',
                    'barangay' => 'Santa Cruz',
                    'street_address' => '214 Roxas Boulevard',
                    'postal_code' => '5800',
                    'latitude' => 11.5856,
                    'longitude' => 122.7514,
                    'services' => ['consultation', 'vaccination', 'surgery', 'small animal surgery'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Cesar Velasquez',
                            'license_number' => 'VET-2024-034',
                            'specialization' => 'Small Animal Surgery'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '18:00'],
                        'thursday' => ['open' => '08:00', 'close' => '18:00'],
                        'friday' => ['open' => '08:00', 'close' => '18:00'],
                        'saturday' => ['open' => '09:00', 'close' => '17:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ],
            [
                'email' => 'info@legazpivetcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Jonathan',
                    'last_name' => 'Sanchez',
                    'phone' => '09621234536',
                    'date_of_birth' => '1985-04-04',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Bicol region veterinarian with coastal community experience.',
                ],
                'clinic' => [
                    'clinic_name' => 'Legazpi Vet Care',
                    'phone' => '052-789-0123',
                    'country' => 'Philippines',
                    'region' => 'Bicol Region',
                    'province' => 'Albay',
                    'city' => 'Legazpi',
                    'barangay' => 'Rawis',
                    'street_address' => '45 Rizal Street',
                    'postal_code' => '4500',
                    'latitude' => 13.1471,
                    'longitude' => 123.7437,
                    'services' => ['consultation', 'vaccination', 'surgery', 'coastal community'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Jonathan Sanchez',
                            'license_number' => 'VET-2024-035',
                            'specialization' => 'Coastal Community Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '19:00'],
                        'thursday' => ['open' => '08:00', 'close' => '19:00'],
                        'friday' => ['open' => '08:00', 'close' => '19:00'],
                        'saturday' => ['open' => '09:00', 'close' => '17:00'],
                        'sunday' => ['open' => '10:00', 'close' => '16:00']
                    ]
                ]
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 BATCH 3 - CLINIC ACCOUNTS:');
        $this->command->info('====================================');

        foreach ($clinics as $clinicData) {
            // Create clinic user account
            $user = User::updateOrCreate(
                ['email' => $clinicData['email']],
                [
                    'password' => Hash::make($clinicData['password']),
                    'account_type' => 'clinic',
                    'is_admin' => false,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'name' => $clinicData['profile']['first_name'] . ' ' . ($clinicData['profile']['last_name'] ?? ''),
                ]
            );

            // Create or update user profile for the clinic owner/admin
            if (method_exists($user, 'profile')) {
                if (!$user->profile) {
                    $user->profile()->create($clinicData['profile']);
                } else {
                    $user->profile->update($clinicData['profile']);
                }
            }

            // Create or update clinic registration (idempotent by email)
            $registration = ClinicRegistration::updateOrCreate(
                ['email' => $clinicData['email']],
                array_merge($clinicData['clinic'], [
                    'user_id' => $user->id,
                    'email' => $clinicData['email'],
                    'certification_proofs' => [],
                    'status' => 'approved',
                    'submitted_at' => now(),
                    'approved_at' => now(),
                    'approved_by' => 1, // Admin user ID
                ])
            );

            $this->command->line("📧 {$clinicData['email']} | 🔑 {$clinicData['password']} | 🏥 {$clinicData['clinic']['clinic_name']}");
        }

        $this->command->info('✅ Batch 3 (10 clinics) seeded successfully!');
        $this->command->info('📊 Total clinics so far: 35 (25 previous + 10 new)');
    }
}
