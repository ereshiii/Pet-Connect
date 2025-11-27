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
        $this->command->info('🏥 Seeding additional clinic accounts - BATCH 5...');

        // BATCH 5: Final Regional Additions (10 clinics)
        $clinics = [
            [
                'email' => 'contact@viganpetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Renato',
                    'last_name' => 'Gonzales',
                    'phone' => '09171234542',
                    'date_of_birth' => '1976-01-01',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Ilocos region veterinarian with heritage town practice experience.',
                ],
                'clinic' => [
                    'clinic_name' => 'Vigan Pet Clinic',
                    'phone' => '077-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Ilocos Region',
                    'province' => 'Ilocos Sur',
                    'city' => 'Vigan',
                    'barangay' => 'Burgos',
                    'street_address' => '11 Crisologo Street',
                    'postal_code' => '2700',
                    'latitude' => 17.5719,
                    'longitude' => 120.3864,
                    'services' => ['consultation', 'vaccination', 'history preservation care'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Renato Gonzales',
                            'license_number' => 'VET-2024-041',
                            'specialization' => 'Heritage Town Care'
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
                'email' => 'info@sanfernandopetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Liza',
                    'last_name' => 'Fernandez',
                    'phone' => '09271234543',
                    'date_of_birth' => '1982-04-30',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Pampanga veterinarian with culinary festival animal care experience.',
                ],
                'clinic' => [
                    'clinic_name' => 'San Fernando Pet Clinic',
                    'phone' => '045-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Central Luzon',
                    'province' => 'Pampanga',
                    'city' => 'San Fernando',
                    'barangay' => 'Solis',
                    'street_address' => '66 Consunji Street',
                    'postal_code' => '2000',
                    'latitude' => 15.0354,
                    'longitude' => 120.6853,
                    'services' => ['consultation', 'vaccination', 'surgery', 'festival support'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Liza Fernandez',
                            'license_number' => 'VET-2024-042',
                            'specialization' => 'Festival Animal Care'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '09:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                        'thursday' => ['open' => '09:00', 'close' => '18:00'],
                        'friday' => ['open' => '09:00', 'close' => '18:00'],
                        'saturday' => ['open' => '09:00', 'close' => '16:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'contact@cabanatuanpetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Manuel',
                    'last_name' => 'Delos Santos',
                    'phone' => '09371234544',
                    'date_of_birth' => '1975-07-07',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Nueva Ecija veterinarian with agriculture-related veterinary services.',
                ],
                'clinic' => [
                    'clinic_name' => 'Cabanatuan Pet Clinic',
                    'phone' => '044-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Central Luzon',
                    'province' => 'Nueva Ecija',
                    'city' => 'Cabanatuan',
                    'barangay' => 'Bagong Iram',
                    'street_address' => '77 Magsaysay Avenue',
                    'postal_code' => '3100',
                    'latitude' => 15.4868,
                    'longitude' => 121.0589,
                    'services' => ['consultation', 'vaccination', 'surgery', 'agriculture services'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Manuel Delos Santos',
                            'license_number' => 'VET-2024-043',
                            'specialization' => 'Agriculture Services'
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
                'email' => 'info@malolosvetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Norberto',
                    'last_name' => 'Santos',
                    'phone' => '09471234545',
                    'date_of_birth' => '1969-03-03',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Bulacan veteran veterinarian with long-standing clinic.',
                ],
                'clinic' => [
                    'clinic_name' => 'Malolos Vet Clinic',
                    'phone' => '044-456-7890',
                    'country' => 'Philippines',
                    'region' => 'Central Luzon',
                    'province' => 'Bulacan',
                    'city' => 'Malolos',
                    'barangay' => 'Poblacion',
                    'street_address' => '23 Del Pilar Street',
                    'postal_code' => '3000',
                    'latitude' => 14.8395,
                    'longitude' => 120.8117,
                    'services' => ['consultation', 'vaccination', 'surgery', 'longstanding clinic'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Norberto Santos',
                            'license_number' => 'VET-2024-044',
                            'specialization' => 'General Practice'
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
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 BATCH 5 - CLINIC ACCOUNTS:');
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

        $this->command->info('✅ Batch 5 (10 clinics) seeded successfully!');
        $this->command->info('📊 Total clinics so far: 55 (45 previous + 10 new)');
    }
}
