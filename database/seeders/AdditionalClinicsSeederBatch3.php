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

        // BATCH 3: Various Regions (10 clinics)
        $clinics = [
            [
                'email' => 'info@laguna petclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Carmela',
                    'last_name' => 'Santos',
                    'phone' => '09121234531',
                    'date_of_birth' => '1979-06-20',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'CALABARZON veterinarian specializing in lake region animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Laguna Pet Clinic',
                    'phone' => '049-234-5678',
                    'country' => 'Philippines',
                    'region' => 'CALABARZON',
                    'province' => 'Laguna',
                    'city' => 'Santa Rosa',
                    'barangay' => 'Tagapo',
                    'street_address' => '741 National Highway',
                    'postal_code' => '4026',
                    'latitude' => 14.3119,
                    'longitude' => 121.1113,
                    'services' => ['consultation', 'vaccination', 'surgery', 'lake care', 'boarding'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Carmela Santos',
                            'license_number' => 'VET-2024-030',
                            'specialization' => 'Lake Region Medicine'
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
                'email' => 'contact@cavite vetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Gregorio',
                    'last_name' => 'Dela Rosa',
                    'phone' => '09231234532',
                    'date_of_birth' => '1976-12-07',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Historical province veterinarian with heritage animal care focus.',
                ],
                'clinic' => [
                    'clinic_name' => 'Cavite Veterinary Center',
                    'phone' => '046-345-6789',
                    'country' => 'Philippines',
                    'region' => 'CALABARZON',
                    'province' => 'Cavite',
                    'city' => 'Imus',
                    'barangay' => 'Poblacion',
                    'street_address' => '852 Aguinaldo Highway',
                    'postal_code' => '4103',
                    'latitude' => 14.4297,
                    'longitude' => 120.9367,
                    'services' => ['consultation', 'vaccination', 'surgery', 'heritage care', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Gregorio Dela Rosa',
                            'license_number' => 'VET-2024-031',
                            'specialization' => 'Heritage Animal Care'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '19:00'],
                        'thursday' => ['open' => '07:00', 'close' => '19:00'],
                        'friday' => ['open' => '07:00', 'close' => '19:00'],
                        'saturday' => ['open' => '08:00', 'close' => '17:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'admin@rizal petcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Luisa',
                    'last_name' => 'Morales',
                    'phone' => '09341234533',
                    'date_of_birth' => '1981-04-25',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Mountain province veterinarian with highland animal expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Rizal Pet Care',
                    'phone' => '02-8456-7890',
                    'country' => 'Philippines',
                    'region' => 'CALABARZON',
                    'province' => 'Rizal',
                    'city' => 'Antipolo',
                    'barangay' => 'Dela Paz',
                    'street_address' => '963 Sumulong Highway',
                    'postal_code' => '1870',
                    'latitude' => 14.5878,
                    'longitude' => 121.1789,
                    'services' => ['consultation', 'vaccination', 'surgery', 'highland care', 'wellness'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Luisa Morales',
                            'license_number' => 'VET-2024-032',
                            'specialization' => 'Highland Animal Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '09:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                        'thursday' => ['open' => '09:00', 'close' => '18:00'],
                        'friday' => ['open' => '09:00', 'close' => '18:00'],
                        'saturday' => ['open' => '09:00', 'close' => '15:00'],
                        'sunday' => ['open' => '10:00', 'close' => '14:00']
                    ]
                ]
            ],
            [
                'email' => 'info@quezon petclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Teodoro',
                    'last_name' => 'Villanueva',
                    'phone' => '09451234534',
                    'date_of_birth' => '1974-08-13',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Quezon province veterinarian with coconut farm animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Quezon Pet Clinic',
                    'phone' => '042-567-8901',
                    'country' => 'Philippines',
                    'region' => 'CALABARZON',
                    'province' => 'Quezon',
                    'city' => 'Lucena',
                    'barangay' => 'Ibabang Dupay',
                    'street_address' => '159 Maharlika Highway',
                    'postal_code' => '4301',
                    'latitude' => 13.9371,
                    'longitude' => 121.6174,
                    'services' => ['consultation', 'vaccination', 'surgery', 'farm care', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Teodoro Villanueva',
                            'license_number' => 'VET-2024-033',
                            'specialization' => 'Farm Animal Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '06:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '06:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '06:00', 'close' => '18:00'],
                        'thursday' => ['open' => '06:00', 'close' => '18:00'],
                        'friday' => ['open' => '06:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ],
            [
                'email' => 'contact@ilocos petcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Remedios',
                    'last_name' => 'Agbayani',
                    'phone' => '09561234535',
                    'date_of_birth' => '1983-02-09',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Ilocos region veterinarian with tobacco farm animal expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Ilocos Pet Care',
                    'phone' => '077-678-9012',
                    'country' => 'Philippines',
                    'region' => 'Ilocos Region',
                    'province' => 'Ilocos Sur',
                    'city' => 'Vigan',
                    'barangay' => 'Poblacion',
                    'street_address' => '753 Liberation Boulevard',
                    'postal_code' => '2700',
                    'latitude' => 17.5748,
                    'longitude' => 120.3869,
                    'services' => ['consultation', 'vaccination', 'surgery', 'heritage care', 'tobacco farm'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Remedios Agbayani',
                            'license_number' => 'VET-2024-034',
                            'specialization' => 'Heritage Medicine'
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
                'email' => 'admin@pangasinan vetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Artemio',
                    'last_name' => 'Lacson',
                    'phone' => '09671234536',
                    'date_of_birth' => '1977-10-31',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Pangasinan veterinarian with salt industry animal care focus.',
                ],
                'clinic' => [
                    'clinic_name' => 'Pangasinan Veterinary Center',
                    'phone' => '075-789-0123',
                    'country' => 'Philippines',
                    'region' => 'Ilocos Region',
                    'province' => 'Pangasinan',
                    'city' => 'Dagupan',
                    'barangay' => 'Poblacion',
                    'street_address' => '456 AB Fernandez Avenue',
                    'postal_code' => '2400',
                    'latitude' => 16.0433,
                    'longitude' => 120.3334,
                    'services' => ['consultation', 'vaccination', 'surgery', 'aquatic', 'salt industry'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Artemio Lacson',
                            'license_number' => 'VET-2024-035',
                            'specialization' => 'Aquatic Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '19:00'],
                        'thursday' => ['open' => '07:00', 'close' => '19:00'],
                        'friday' => ['open' => '07:00', 'close' => '19:00'],
                        'saturday' => ['open' => '08:00', 'close' => '17:00'],
                        'sunday' => ['open' => '08:00', 'close' => '17:00']
                    ]
                ]
            ],
            [
                'email' => 'info@pampanga petclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Consuelo',
                    'last_name' => 'Manalo',
                    'phone' => '09781234537',
                    'date_of_birth' => '1980-01-17',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Central Luzon veterinarian with flood-prone area expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Pampanga Pet Clinic',
                    'phone' => '045-890-1234',
                    'country' => 'Philippines',
                    'region' => 'Central Luzon',
                    'province' => 'Pampanga',
                    'city' => 'San Fernando',
                    'barangay' => 'Del Rosario',
                    'street_address' => '789 MacArthur Highway',
                    'postal_code' => '2000',
                    'latitude' => 15.0255,
                    'longitude' => 120.6893,
                    'services' => ['consultation', 'vaccination', 'surgery', 'flood care', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Consuelo Manalo',
                            'license_number' => 'VET-2024-036',
                            'specialization' => 'Flood Area Medicine'
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
                'email' => 'contact@bulacan vetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Emilio',
                    'last_name' => 'Francisco',
                    'phone' => '09891234538',
                    'date_of_birth' => '1978-07-04',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Bulacan veterinarian with industrial zone animal care focus.',
                ],
                'clinic' => [
                    'clinic_name' => 'Bulacan Veterinary Center',
                    'phone' => '044-901-2345',
                    'country' => 'Philippines',
                    'region' => 'Central Luzon',
                    'province' => 'Bulacan',
                    'city' => 'Malolos',
                    'barangay' => 'Poblacion',
                    'street_address' => '321 McArthur Highway',
                    'postal_code' => '3000',
                    'latitude' => 14.8433,
                    'longitude' => 120.8110,
                    'services' => ['consultation', 'vaccination', 'surgery', 'industrial care', 'laboratory'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Emilio Francisco',
                            'license_number' => 'VET-2024-037',
                            'specialization' => 'Industrial Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '09:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                        'thursday' => ['open' => '09:00', 'close' => '18:00'],
                        'friday' => ['open' => '09:00', 'close' => '18:00'],
                        'saturday' => ['open' => '09:00', 'close' => '15:00'],
                        'sunday' => ['open' => '10:00', 'close' => '14:00']
                    ]
                ]
            ],
            [
                'email' => 'admin@nueva ecijaanimalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Violeta',
                    'last_name' => 'Aquino',
                    'phone' => '09901234539',
                    'date_of_birth' => '1982-03-12',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Rice granary veterinarian with agricultural animal expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Nueva Ecija Animal Hospital',
                    'phone' => '044-012-3456',
                    'country' => 'Philippines',
                    'region' => 'Central Luzon',
                    'province' => 'Nueva Ecija',
                    'city' => 'Cabanatuan',
                    'barangay' => 'Poblacion',
                    'street_address' => '654 Maharlika Highway',
                    'postal_code' => '3100',
                    'latitude' => 15.4911,
                    'longitude' => 120.9621,
                    'services' => ['consultation', 'vaccination', 'surgery', 'agricultural', 'livestock'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Violeta Aquino',
                            'license_number' => 'VET-2024-038',
                            'specialization' => 'Agricultural Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '06:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '06:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '06:00', 'close' => '18:00'],
                        'thursday' => ['open' => '06:00', 'close' => '18:00'],
                        'friday' => ['open' => '06:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'info@tarla cpetcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Nestor',
                    'last_name' => 'Galvez',
                    'phone' => '09111234540',
                    'date_of_birth' => '1975-11-26',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Central Luzon veterinarian with sugar plantation animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Tarlac Pet Care',
                    'phone' => '045-123-4567',
                    'country' => 'Philippines',
                    'region' => 'Central Luzon',
                    'province' => 'Tarlac',
                    'city' => 'Tarlac City',
                    'barangay' => 'San Vicente',
                    'street_address' => '987 Romulo Highway',
                    'postal_code' => '2300',
                    'latitude' => 15.4754,
                    'longitude' => 120.5969,
                    'services' => ['consultation', 'vaccination', 'surgery', 'plantation care', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Nestor Galvez',
                            'license_number' => 'VET-2024-039',
                            'specialization' => 'Plantation Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '19:00'],
                        'thursday' => ['open' => '07:00', 'close' => '19:00'],
                        'friday' => ['open' => '07:00', 'close' => '19:00'],
                        'saturday' => ['open' => '08:00', 'close' => '17:00'],
                        'sunday' => ['open' => '08:00', 'close' => '17:00']
                    ]
                ]
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 BATCH 3 - CLINIC ACCOUNTS:');
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

        $this->command->info('✅ Batch 3 (10 clinics) seeded successfully!');
        $this->command->info('📊 Total clinics so far: 35 (5 original + 30 additional)');
    }
}