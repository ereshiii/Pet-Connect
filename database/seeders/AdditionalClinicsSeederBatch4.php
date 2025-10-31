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

        // BATCH 4: Various Regions (10 clinics)
        $clinics = [
            [
                'email' => 'contact@samar petclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Florencia',
                    'last_name' => 'Gutierrez',
                    'phone' => '09121234541',
                    'date_of_birth' => '1979-08-19',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Eastern Visayas island veterinarian with coastal animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Samar Pet Clinic',
                    'phone' => '055-234-5678',
                    'country' => 'Philippines',
                    'region' => 'Eastern Visayas',
                    'province' => 'Samar',
                    'city' => 'Catbalogan',
                    'barangay' => 'Poblacion',
                    'street_address' => '159 Allen Avenue',
                    'postal_code' => '6700',
                    'latitude' => 11.7743,
                    'longitude' => 124.8810,
                    'services' => ['consultation', 'vaccination', 'surgery', 'coastal care', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Florencia Gutierrez',
                            'license_number' => 'VET-2024-040',
                            'specialization' => 'Coastal Animal Medicine'
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
                'email' => 'info@siquijor vetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Maximo',
                    'last_name' => 'Espiritu',
                    'phone' => '09231234542',
                    'date_of_birth' => '1976-05-03',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Mystical island veterinarian with traditional healing knowledge.',
                ],
                'clinic' => [
                    'clinic_name' => 'Siquijor Veterinary Center',
                    'phone' => '035-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Central Visayas',
                    'province' => 'Siquijor',
                    'city' => 'Siquijor',
                    'barangay' => 'Poblacion',
                    'street_address' => '753 Circumferential Road',
                    'postal_code' => '6226',
                    'latitude' => 9.2142,
                    'longitude' => 123.5041,
                    'services' => ['consultation', 'vaccination', 'surgery', 'traditional care', 'herbs'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Maximo Espiritu',
                            'license_number' => 'VET-2024-041',
                            'specialization' => 'Traditional Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '18:00'],
                        'thursday' => ['open' => '07:00', 'close' => '18:00'],
                        'friday' => ['open' => '07:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ],
            [
                'email' => 'admin@masbate petcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Felicidad',
                    'last_name' => 'Caballero',
                    'phone' => '09341234543',
                    'date_of_birth' => '1981-12-11',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Cattle island veterinarian with livestock expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Masbate Pet Care',
                    'phone' => '056-456-7890',
                    'country' => 'Philippines',
                    'region' => 'Bicol Region',
                    'province' => 'Masbate',
                    'city' => 'Masbate City',
                    'barangay' => 'Poblacion',
                    'street_address' => '456 Quezon Street',
                    'postal_code' => '5400',
                    'latitude' => 12.3685,
                    'longitude' => 123.6198,
                    'services' => ['consultation', 'vaccination', 'surgery', 'cattle care', 'livestock'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Felicidad Caballero',
                            'license_number' => 'VET-2024-042',
                            'specialization' => 'Cattle Medicine'
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
                'email' => 'contact@romblon animalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Honorio',
                    'last_name' => 'Mendez',
                    'phone' => '09451234544',
                    'date_of_birth' => '1974-04-27',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Marble province veterinarian with quarry area animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Romblon Animal Hospital',
                    'phone' => '042-567-8901',
                    'country' => 'Philippines',
                    'region' => 'MIMAROPA',
                    'province' => 'Romblon',
                    'city' => 'Romblon',
                    'barangay' => 'Poblacion',
                    'street_address' => '789 Provincial Road',
                    'postal_code' => '5500',
                    'latitude' => 12.5778,
                    'longitude' => 122.2687,
                    'services' => ['consultation', 'vaccination', 'surgery', 'quarry care', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Honorio Mendez',
                            'license_number' => 'VET-2024-043',
                            'specialization' => 'Quarry Area Medicine'
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
                'email' => 'info@mindoro vetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Esperanza',
                    'last_name' => 'Mercado',
                    'phone' => '09561234545',
                    'date_of_birth' => '1983-07-08',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Mindoro veterinarian with tribal community animal care focus.',
                ],
                'clinic' => [
                    'clinic_name' => 'Mindoro Veterinary Clinic',
                    'phone' => '043-678-9012',
                    'country' => 'Philippines',
                    'region' => 'MIMAROPA',
                    'province' => 'Oriental Mindoro',
                    'city' => 'Calapan',
                    'barangay' => 'Poblacion',
                    'street_address' => '321 Jp Rizal Street',
                    'postal_code' => '5200',
                    'latitude' => 13.4118,
                    'longitude' => 121.1803,
                    'services' => ['consultation', 'vaccination', 'surgery', 'tribal care', 'community'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Esperanza Mercado',
                            'license_number' => 'VET-2024-044',
                            'specialization' => 'Tribal Community Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '09:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '09:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '09:00', 'close' => '17:00'],
                        'thursday' => ['open' => '09:00', 'close' => '17:00'],
                        'friday' => ['open' => '09:00', 'close' => '17:00'],
                        'saturday' => ['open' => '09:00', 'close' => '15:00'],
                        'sunday' => ['open' => '10:00', 'close' => '14:00']
                    ]
                ]
            ],
            [
                'email' => 'admin@kalinga vetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Dionisio',
                    'last_name' => 'Balangue',
                    'phone' => '09671234546',
                    'date_of_birth' => '1977-01-14',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Cordillera veterinarian with mountain tribal animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Kalinga Veterinary Center',
                    'phone' => '074-789-0123',
                    'country' => 'Philippines',
                    'region' => 'Cordillera Administrative Region',
                    'province' => 'Kalinga',
                    'city' => 'Tabuk',
                    'barangay' => 'Poblacion',
                    'street_address' => '654 Provincial Road',
                    'postal_code' => '3800',
                    'latitude' => 17.4189,
                    'longitude' => 121.4444,
                    'services' => ['consultation', 'vaccination', 'surgery', 'mountain care', 'tribal'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Dionisio Balangue',
                            'license_number' => 'VET-2024-045',
                            'specialization' => 'Mountain Medicine'
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
                'email' => 'contact@ifugao petcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Corazon',
                    'last_name' => 'Dumulag',
                    'phone' => '09781234547',
                    'date_of_birth' => '1980-09-21',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Rice terraces veterinarian with heritage animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Ifugao Pet Care',
                    'phone' => '074-890-1234',
                    'country' => 'Philippines',
                    'region' => 'Cordillera Administrative Region',
                    'province' => 'Ifugao',
                    'city' => 'Lagawe',
                    'barangay' => 'Poblacion',
                    'street_address' => '987 Heritage Road',
                    'postal_code' => '3600',
                    'latitude' => 16.7848,
                    'longitude' => 121.1239,
                    'services' => ['consultation', 'vaccination', 'surgery', 'heritage care', 'terraces'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Corazon Dumulag',
                            'license_number' => 'VET-2024-046',
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
                'email' => 'info@abra animalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Macario',
                    'last_name' => 'Valera',
                    'phone' => '09891234548',
                    'date_of_birth' => '1978-02-06',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Abra valley veterinarian with river basin animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Abra Animal Hospital',
                    'phone' => '074-901-2345',
                    'country' => 'Philippines',
                    'region' => 'Cordillera Administrative Region',
                    'province' => 'Abra',
                    'city' => 'Bangued',
                    'barangay' => 'Poblacion',
                    'street_address' => '147 Abra River Road',
                    'postal_code' => '2800',
                    'latitude' => 17.5971,
                    'longitude' => 120.6204,
                    'services' => ['consultation', 'vaccination', 'surgery', 'river care', 'valley'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Macario Valera',
                            'license_number' => 'VET-2024-047',
                            'specialization' => 'River Basin Medicine'
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
                'email' => 'admin@apayao vetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Remedios',
                    'last_name' => 'Bumacod',
                    'phone' => '09901234549',
                    'date_of_birth' => '1982-06-13',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Remote mountain veterinarian with wilderness animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Apayao Veterinary Clinic',
                    'phone' => '074-012-3456',
                    'country' => 'Philippines',
                    'region' => 'Cordillera Administrative Region',
                    'province' => 'Apayao',
                    'city' => 'Kabugao',
                    'barangay' => 'Poblacion',
                    'street_address' => '258 Mountain Highway',
                    'postal_code' => '3800',
                    'latitude' => 17.8431,
                    'longitude' => 121.1239,
                    'services' => ['consultation', 'vaccination', 'emergency', 'wilderness', 'remote care'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Remedios Bumacod',
                            'license_number' => 'VET-2024-048',
                            'specialization' => 'Wilderness Medicine'
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
                'email' => 'contact@mountain province petcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Catalino',
                    'last_name' => 'Fianza',
                    'phone' => '09111234550',
                    'date_of_birth' => '1975-10-29',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'High altitude veterinarian with specialty in highland breeds.',
                ],
                'clinic' => [
                    'clinic_name' => 'Mountain Province Pet Care',
                    'phone' => '074-123-4567',
                    'country' => 'Philippines',
                    'region' => 'Cordillera Administrative Region',
                    'province' => 'Mountain Province',
                    'city' => 'Bontoc',
                    'barangay' => 'Poblacion',
                    'street_address' => '369 Chico River Road',
                    'postal_code' => '2616',
                    'latitude' => 17.0896,
                    'longitude' => 120.9763,
                    'services' => ['consultation', 'vaccination', 'surgery', 'altitude care', 'highland'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Catalino Fianza',
                            'license_number' => 'VET-2024-049',
                            'specialization' => 'High Altitude Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '18:00'],
                        'thursday' => ['open' => '07:00', 'close' => '18:00'],
                        'friday' => ['open' => '07:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 BATCH 4 - CLINIC ACCOUNTS:');
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

        $this->command->info('✅ Batch 4 (10 clinics) seeded successfully!');
        $this->command->info('📊 Total clinics so far: 45 (5 original + 40 additional)');
    }
}