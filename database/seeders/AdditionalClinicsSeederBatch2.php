<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClinicRegistration;
use Illuminate\Support\Facades\Hash;

class AdditionalClinicsSeederBatch2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 Seeding additional clinic accounts - BATCH 2...');

        // BATCH 2: Various Regions (10 clinics)
        $clinics = [
            [
                'email' => 'contact@boholpetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Patricia',
                    'last_name' => 'Jimenez',
                    'phone' => '09121234521',
                    'date_of_birth' => '1979-04-08',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Island veterinarian specializing in tourist area animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Bohol Pet Center',
                    'phone' => '038-234-5678',
                    'country' => 'Philippines',
                    'region' => 'Central Visayas',
                    'province' => 'Bohol',
                    'city' => 'Tagbilaran',
                    'barangay' => 'Cogon',
                    'street_address' => '159 CPG Avenue',
                    'postal_code' => '6300',
                    'latitude' => 9.6496,
                    'longitude' => 123.8547,
                    'services' => ['consultation', 'vaccination', 'surgery', 'emergency', 'tourism pet care'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Patricia Jimenez',
                            'license_number' => 'VET-2024-020',
                            'specialization' => 'Tourist Area Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '20:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                        'thursday' => ['open' => '08:00', 'close' => '20:00'],
                        'friday' => ['open' => '08:00', 'close' => '20:00'],
                        'saturday' => ['open' => '08:00', 'close' => '20:00'],
                        'sunday' => ['open' => '08:00', 'close' => '20:00']
                    ]
                ]
            ],
            [
                'email' => 'info@negrosanimalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Gabriel',
                    'last_name' => 'Soriano',
                    'phone' => '09231234522',
                    'date_of_birth' => '1976-10-15',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Sugar industry region veterinarian with livestock expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Negros Animal Hospital',
                    'phone' => '034-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Western Visayas',
                    'province' => 'Negros Occidental',
                    'city' => 'Bacolod',
                    'barangay' => 'Mandalagan',
                    'street_address' => '753 Lacson Street',
                    'postal_code' => '6100',
                    'latitude' => 10.6767,
                    'longitude' => 122.9554,
                    'services' => ['consultation', 'vaccination', 'surgery', 'livestock', 'laboratory'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Gabriel Soriano',
                            'license_number' => 'VET-2024-021',
                            'specialization' => 'Livestock Medicine'
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
                'email' => 'admin@butuan vetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Rosario',
                    'last_name' => 'Pascual',
                    'phone' => '09341234523',
                    'date_of_birth' => '1981-01-22',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Caraga region veterinarian specializing in wetland animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Butuan Veterinary Clinic',
                    'phone' => '085-456-7890',
                    'country' => 'Philippines',
                    'region' => 'Caraga',
                    'province' => 'Agusan del Norte',
                    'city' => 'Butuan',
                    'barangay' => 'Libertad',
                    'street_address' => '456 J.C. Aquino Avenue',
                    'postal_code' => '8600',
                    'latitude' => 8.9470,
                    'longitude' => 125.5436,
                    'services' => ['consultation', 'vaccination', 'surgery', 'wetland medicine', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Rosario Pascual',
                            'license_number' => 'VET-2024-022',
                            'specialization' => 'Wetland Animal Medicine'
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
                'email' => 'contact@surigaopetcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Rodrigo',
                    'last_name' => 'Navarro',
                    'phone' => '09451234524',
                    'date_of_birth' => '1974-07-11',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Northeastern Mindanao veterinarian with mining area expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Surigao Pet Care',
                    'phone' => '086-567-8901',
                    'country' => 'Philippines',
                    'region' => 'Caraga',
                    'province' => 'Surigao del Norte',
                    'city' => 'Surigao City',
                    'barangay' => 'Washington',
                    'street_address' => '789 Borromeo Street',
                    'postal_code' => '8400',
                    'latitude' => 9.7871,
                    'longitude' => 125.4919,
                    'services' => ['consultation', 'vaccination', 'surgery', 'mining area care', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Rodrigo Navarro',
                            'license_number' => 'VET-2024-023',
                            'specialization' => 'Mining Area Medicine'
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
                'email' => 'info@caminiguinvetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Esperanza',
                    'last_name' => 'Salazar',
                    'phone' => '09561234525',
                    'date_of_birth' => '1983-03-18',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Small island veterinarian specializing in remote area animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Camiguin Veterinary Center',
                    'phone' => '088-678-9012',
                    'country' => 'Philippines',
                    'region' => 'Northern Mindanao',
                    'province' => 'Camiguin',
                    'city' => 'Mambajao',
                    'barangay' => 'Poblacion',
                    'street_address' => '321 National Highway',
                    'postal_code' => '9100',
                    'latitude' => 9.2496,
                    'longitude' => 124.7294,
                    'services' => ['consultation', 'vaccination', 'emergency', 'remote care', 'island medicine'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Esperanza Salazar',
                            'license_number' => 'VET-2024-024',
                            'specialization' => 'Remote Area Medicine'
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
                'email' => 'admin@cagayandeoroanimalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Eduardo',
                    'last_name' => 'Velasco',
                    'phone' => '09671234526',
                    'date_of_birth' => '1977-12-05',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Northern Mindanao urban veterinarian with specialty clinic experience.',
                ],
                'clinic' => [
                    'clinic_name' => 'Cagayan de Oro Animal Hospital',
                    'phone' => '088-789-0123',
                    'country' => 'Philippines',
                    'region' => 'Northern Mindanao',
                    'province' => 'Misamis Oriental',
                    'city' => 'Cagayan de Oro',
                    'barangay' => 'Nazareth',
                    'street_address' => '654 Corrales Avenue',
                    'postal_code' => '9000',
                    'latitude' => 8.4542,
                    'longitude' => 124.6319,
                    'services' => ['consultation', 'vaccination', 'surgery', 'imaging', 'speciality care'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Eduardo Velasco',
                            'license_number' => 'VET-2024-025',
                            'specialization' => 'Specialty Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '21:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '21:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '21:00'],
                        'thursday' => ['open' => '07:00', 'close' => '21:00'],
                        'friday' => ['open' => '07:00', 'close' => '21:00'],
                        'saturday' => ['open' => '08:00', 'close' => '19:00'],
                        'sunday' => ['open' => '08:00', 'close' => '19:00']
                    ]
                ]
            ],
            [
                'email' => 'contact@taclobanpetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Margarita',
                    'last_name' => 'Domingo',
                    'phone' => '09781234527',
                    'date_of_birth' => '1980-08-28',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Eastern Visayas veterinarian with disaster response expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Tacloban Pet Clinic',
                    'phone' => '053-890-1234',
                    'country' => 'Philippines',
                    'region' => 'Eastern Visayas',
                    'province' => 'Leyte',
                    'city' => 'Tacloban',
                    'barangay' => 'Downtown',
                    'street_address' => '987 Real Street',
                    'postal_code' => '6500',
                    'latitude' => 11.2421,
                    'longitude' => 125.0049,
                    'services' => ['consultation', 'vaccination', 'surgery', 'emergency', 'disaster response'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Margarita Domingo',
                            'license_number' => 'VET-2024-026',
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
                'email' => 'info@dumaguetevetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Benjamin',
                    'last_name' => 'Aguilar',
                    'phone' => '09891234528',
                    'date_of_birth' => '1978-05-14',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'University town veterinarian with research and academic focus.',
                ],
                'clinic' => [
                    'clinic_name' => 'Dumaguete Veterinary Center',
                    'phone' => '035-901-2345',
                    'country' => 'Philippines',
                    'region' => 'Central Visayas',
                    'province' => 'Negros Oriental',
                    'city' => 'Dumaguete',
                    'barangay' => 'Poblacion',
                    'street_address' => '147 Rizal Boulevard',
                    'postal_code' => '6200',
                    'latitude' => 9.3077,
                    'longitude' => 123.3046,
                    'services' => ['consultation', 'vaccination', 'surgery', 'research', 'academic'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Benjamin Aguilar',
                            'license_number' => 'VET-2024-027',
                            'specialization' => 'Academic Medicine'
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
                'email' => 'admin@marawi petcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Fatima',
                    'last_name' => 'Abdullah',
                    'phone' => '09901234529',
                    'date_of_birth' => '1982-11-09',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Lanao del Sur veterinarian serving Islamic community animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Marawi Pet Care',
                    'phone' => '063-012-3456',
                    'country' => 'Philippines',
                    'region' => 'Bangsamoro Autonomous Region',
                    'province' => 'Lanao del Sur',
                    'city' => 'Marawi',
                    'barangay' => 'Poblacion',
                    'street_address' => '258 Quezon Avenue',
                    'postal_code' => '9700',
                    'latitude' => 8.0021,
                    'longitude' => 124.2932,
                    'services' => ['consultation', 'vaccination', 'surgery', 'halal care', 'community'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Fatima Abdullah',
                            'license_number' => 'VET-2024-028',
                            'specialization' => 'Community Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '17:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                        'thursday' => ['open' => '08:00', 'close' => '17:00'],
                        'friday' => ['open' => '14:00', 'close' => '17:00'], // Adjusted for Friday prayers
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => '09:00', 'close' => '15:00']
                    ]
                ]
            ],
            [
                'email' => 'contact@generalsan tosanimalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Alfonso',
                    'last_name' => 'Rivera',
                    'phone' => '09111234530',
                    'date_of_birth' => '1975-09-16',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'SOCCSKSARGEN region veterinarian with tuna industry expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'General Santos Animal Hospital',
                    'phone' => '083-123-4567',
                    'country' => 'Philippines',
                    'region' => 'SOCCSKSARGEN',
                    'province' => 'South Cotabato',
                    'city' => 'General Santos',
                    'barangay' => 'City Heights',
                    'street_address' => '369 Pioneer Avenue',
                    'postal_code' => '9500',
                    'latitude' => 6.1164,
                    'longitude' => 125.1716,
                    'services' => ['consultation', 'vaccination', 'surgery', 'aquaculture', 'industrial'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Alfonso Rivera',
                            'license_number' => 'VET-2024-029',
                            'specialization' => 'Aquaculture Medicine'
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
        $this->command->info('🔐 BATCH 2 - CLINIC ACCOUNTS:');
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

        $this->command->info('✅ Batch 2 (10 clinics) seeded successfully!');
        $this->command->info('📊 Total clinics so far: 25 (5 original + 20 additional)');
    }
}