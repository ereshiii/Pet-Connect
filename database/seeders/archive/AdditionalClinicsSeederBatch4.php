<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClinicRegistration;
use Illuminate\Support\Facades\Hash;

class AdditionalClinicsSeederBatch4 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 Seeding additional clinic accounts - BATCH 4...');

        // BATCH 4: Mindanao Expansion (10 clinics)
        $clinics = [
            [
                'email' => 'contact@zamboangavet.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Mateo',
                    'last_name' => 'Reyes',
                    'phone' => '09161234537',
                    'date_of_birth' => '1981-05-05',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Zamboanga peninsula veterinarian with regional network experience.',
                ],
                'clinic' => [
                    'clinic_name' => 'Zamboanga Vet Clinic',
                    'phone' => '062-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Zamboanga Peninsula',
                    'province' => 'Zamboanga del Sur',
                    'city' => 'Zamboanga City',
                    'barangay' => 'Tugbungan',
                    'street_address' => '321 Veterans Avenue',
                    'postal_code' => '7000',
                    'latitude' => 6.9200,
                    'longitude' => 122.0790,
                    'services' => ['consultation', 'vaccination', 'surgery', 'community outreach'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Mateo Reyes',
                            'license_number' => 'VET-2024-036',
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
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'info@dipologpetcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Wilfred',
                    'last_name' => 'Lazaro',
                    'phone' => '09221234538',
                    'date_of_birth' => '1979-02-02',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Dipolog veterinarian serving coastal communities.',
                ],
                'clinic' => [
                    'clinic_name' => 'Dipolog Pet Care',
                    'phone' => '065-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Zamboanga Peninsula',
                    'province' => 'Zamboanga del Norte',
                    'city' => 'Dipolog',
                    'barangay' => 'Central',
                    'street_address' => '98 Rizal Avenue',
                    'postal_code' => '7100',
                    'latitude' => 8.5818,
                    'longitude' => 123.3488,
                    'services' => ['consultation', 'vaccination', 'surgery', 'coastal care'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Wilfred Lazaro',
                            'license_number' => 'VET-2024-037',
                            'specialization' => 'Coastal Care'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '08:00', 'close' => '15:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'contact@iliganpetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Agnes',
                    'last_name' => 'Torres',
                    'phone' => '09321234539',
                    'date_of_birth' => '1987-08-08',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Iligan City veterinarian with emergency care focus.',
                ],
                'clinic' => [
                    'clinic_name' => 'Iligan Pet Clinic',
                    'phone' => '063-456-7890',
                    'country' => 'Philippines',
                    'region' => 'Northern Mindanao',
                    'province' => 'Lanao del Norte',
                    'city' => 'Iligan City',
                    'barangay' => 'Poblacion',
                    'street_address' => '77 Mabini Street',
                    'postal_code' => '9200',
                    'latitude' => 8.2293,
                    'longitude' => 124.2450,
                    'services' => ['consultation', 'vaccination', 'surgery', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Agnes Torres',
                            'license_number' => 'VET-2024-038',
                            'specialization' => 'Emergency Care'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '19:00'],
                        'thursday' => ['open' => '07:00', 'close' => '19:00'],
                        'friday' => ['open' => '07:00', 'close' => '19:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'info@tandagpetcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Lino',
                    'last_name' => 'Garcia',
                    'phone' => '09431234540',
                    'date_of_birth' => '1971-11-11',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Southeastern Mindanao veterinarian with rural outreach programs.',
                ],
                'clinic' => [
                    'clinic_name' => 'Tandag Pet Care',
                    'phone' => '086-567-8901',
                    'country' => 'Philippines',
                    'region' => 'Caraga',
                    'province' => 'Surigao del Sur',
                    'city' => 'Tandag',
                    'barangay' => 'Poblacion',
                    'street_address' => '142 Rizal Street',
                    'postal_code' => '8310',
                    'latitude' => 9.1485,
                    'longitude' => 126.1742,
                    'services' => ['consultation', 'vaccination', 'surgery', 'rural outreach'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Lino Garcia',
                            'license_number' => 'VET-2024-039',
                            'specialization' => 'Rural Outreach'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '08:00', 'close' => '17:00'],
                        'saturday' => ['open' => '09:00', 'close' => '15:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'admin@general santos petcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Hector',
                    'last_name' => 'Santos',
                    'phone' => '09541234541',
                    'date_of_birth' => '1973-06-23',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'General Santos veterinarian experienced with large animals and aquaculture.',
                ],
                'clinic' => [
                    'clinic_name' => 'General Santos Pet Care',
                    'phone' => '083-789-0123',
                    'country' => 'Philippines',
                    'region' => 'SOCCSKSARGEN',
                    'province' => 'South Cotabato',
                    'city' => 'General Santos',
                    'barangay' => 'City Heights',
                    'street_address' => '951 Pioneer Avenue',
                    'postal_code' => '9500',
                    'latitude' => 6.1122,
                    'longitude' => 125.1710,
                    'services' => ['consultation', 'vaccination', 'surgery', 'aquaculture', 'livestock'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Hector Santos',
                            'license_number' => 'VET-2024-040',
                            'specialization' => 'Aquaculture & Livestock'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '06:00', 'close' => '20:00'],
                        'tuesday' => ['open' => '06:00', 'close' => '20:00'],
                        'wednesday' => ['open' => '06:00', 'close' => '20:00'],
                        'thursday' => ['open' => '06:00', 'close' => '20:00'],
                        'friday' => ['open' => '06:00', 'close' => '20:00'],
                        'saturday' => ['open' => '08:00', 'close' => '18:00'],
                        'sunday' => ['open' => '08:00', 'close' => '18:00']
                    ]
                ]
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 BATCH 4 - CLINIC ACCOUNTS:');
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

        $this->command->info('✅ Batch 4 (10 clinics) seeded successfully!');
        $this->command->info('📊 Total clinics so far: 45 (35 previous + 10 new)');
    }
}
