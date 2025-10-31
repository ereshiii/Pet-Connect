<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClinicRegistration;
use Illuminate\Support\Facades\Hash;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 Seeding clinic accounts...');

        $clinics = [
            [
                'email' => 'info@amorpetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Maria',
                    'last_name' => 'Gonzales',
                    'phone' => '09171234501',
                    'date_of_birth' => '1980-03-15',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Lead veterinarian at Amor Pet Clinic, specializing in small animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Amor Pet Clinic',
                    'phone' => '02-8123-4567',
                    'country' => 'Philippines',
                    'region' => 'National Capital Region',
                    'province' => 'Metro Manila',
                    'city' => 'Makati',
                    'barangay' => 'Poblacion',
                    'street_address' => '123 Ayala Avenue',
                    'postal_code' => '1226',
                    'latitude' => 14.5547,
                    'longitude' => 121.0244,
                    'services' => ['consultation', 'vaccination', 'surgery', 'grooming', 'boarding'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Maria Gonzales',
                            'license_number' => 'VET-2024-001',
                            'specialization' => 'Small Animal Medicine'
                        ],
                        [
                            'name' => 'Dr. Roberto Santos',
                            'license_number' => 'VET-2024-002',
                            'specialization' => 'Veterinary Surgery'
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
                'email' => 'contact@pawprintsclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Juan',
                    'last_name' => 'dela Cruz',
                    'phone' => '09281234502',
                    'date_of_birth' => '1978-07-22',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Experienced veterinarian specializing in exotic pets and emergency care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Paw Prints Veterinary Clinic',
                    'phone' => '02-8234-5678',
                    'country' => 'Philippines',
                    'region' => 'National Capital Region',
                    'province' => 'Metro Manila',
                    'city' => 'Quezon City',
                    'barangay' => 'Diliman',
                    'street_address' => '456 Commonwealth Avenue',
                    'postal_code' => '1121',
                    'latitude' => 14.6507,
                    'longitude' => 121.0318,
                    'services' => ['consultation', 'vaccination', 'surgery', 'emergency', 'dental'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Juan dela Cruz',
                            'license_number' => 'VET-2024-003',
                            'specialization' => 'Exotic Animal Medicine'
                        ],
                        [
                            'name' => 'Dr. Elena Reyes',
                            'license_number' => 'VET-2024-004',
                            'specialization' => 'Emergency Medicine'
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
                'email' => 'admin@healthypetsclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Ana',
                    'last_name' => 'Mercado',
                    'phone' => '09391234503',
                    'date_of_birth' => '1982-11-08',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Holistic veterinarian focusing on preventive care and wellness.',
                ],
                'clinic' => [
                    'clinic_name' => 'Healthy Pets Veterinary Center',
                    'phone' => '02-8345-6789',
                    'country' => 'Philippines',
                    'region' => 'National Capital Region',
                    'province' => 'Metro Manila',
                    'city' => 'Pasig',
                    'barangay' => 'Ortigas Center',
                    'street_address' => '789 Ortigas Avenue',
                    'postal_code' => '1605',
                    'latitude' => 14.5864,
                    'longitude' => 121.0562,
                    'services' => ['consultation', 'vaccination', 'wellness', 'nutrition', 'behavioral'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Ana Mercado',
                            'license_number' => 'VET-2024-005',
                            'specialization' => 'Preventive Medicine'
                        ],
                        [
                            'name' => 'Dr. Carlos Fernandez',
                            'license_number' => 'VET-2024-006',
                            'specialization' => 'Animal Nutrition'
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
                'email' => 'info@bestfriendsclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Miguel',
                    'last_name' => 'Torres',
                    'phone' => '09451234504',
                    'date_of_birth' => '1975-05-30',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Senior veterinarian with 20+ years experience in companion animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Best Friends Animal Hospital',
                    'phone' => '02-8456-7890',
                    'country' => 'Philippines',
                    'region' => 'National Capital Region',
                    'province' => 'Metro Manila',
                    'city' => 'Taguig',
                    'barangay' => 'Bonifacio Global City',
                    'street_address' => '321 High Street',
                    'postal_code' => '1634',
                    'latitude' => 14.5514,
                    'longitude' => 121.0496,
                    'services' => ['consultation', 'vaccination', 'surgery', 'imaging', 'laboratory'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Miguel Torres',
                            'license_number' => 'VET-2024-007',
                            'specialization' => 'General Practice'
                        ],
                        [
                            'name' => 'Dr. Sofia Valdez',
                            'license_number' => 'VET-2024-008',
                            'specialization' => 'Diagnostic Imaging'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '06:00', 'close' => '22:00'],
                        'tuesday' => ['open' => '06:00', 'close' => '22:00'],
                        'wednesday' => ['open' => '06:00', 'close' => '22:00'],
                        'thursday' => ['open' => '06:00', 'close' => '22:00'],
                        'friday' => ['open' => '06:00', 'close' => '22:00'],
                        'saturday' => ['open' => '08:00', 'close' => '20:00'],
                        'sunday' => ['open' => '08:00', 'close' => '20:00']
                    ]
                ]
            ],
            [
                'email' => 'contact@carefulpawsclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Luz',
                    'last_name' => 'Ramos',
                    'phone' => '09561234505',
                    'date_of_birth' => '1984-09-12',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Compassionate veterinarian dedicated to providing gentle care for all pets.',
                ],
                'clinic' => [
                    'clinic_name' => 'Careful Paws Veterinary Clinic',
                    'phone' => '02-8567-8901',
                    'country' => 'Philippines',
                    'region' => 'National Capital Region',
                    'province' => 'Metro Manila',
                    'city' => 'Mandaluyong',
                    'barangay' => 'Wack Wack',
                    'street_address' => '654 Shaw Boulevard',
                    'postal_code' => '1552',
                    'latitude' => 14.5863,
                    'longitude' => 121.0581,
                    'services' => ['consultation', 'vaccination', 'grooming', 'pharmacy', 'pet supplies'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Luz Ramos',
                            'license_number' => 'VET-2024-009',
                            'specialization' => 'Small Animal Practice'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '10:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '10:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '10:00', 'close' => '19:00'],
                        'thursday' => ['open' => '10:00', 'close' => '19:00'],
                        'friday' => ['open' => '10:00', 'close' => '19:00'],
                        'saturday' => ['open' => '09:00', 'close' => '18:00'],
                        'sunday' => ['open' => '09:00', 'close' => '18:00']
                    ]
                ]
            ],
            // BATCH 1: Luzon Regions (10 clinics)
            [
                'email' => 'info@cebuvetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Rosa',
                    'last_name' => 'Villamor',
                    'phone' => '09171234511',
                    'date_of_birth' => '1979-01-15',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Experienced veterinarian specializing in tropical animal medicine.',
                ],
                'clinic' => [
                    'clinic_name' => 'Cebu Veterinary Center',
                    'phone' => '032-234-5678',
                    'country' => 'Philippines',
                    'region' => 'Central Visayas',
                    'province' => 'Cebu',
                    'city' => 'Cebu City',
                    'barangay' => 'Lahug',
                    'street_address' => '456 JY Square Mall',
                    'postal_code' => '6000',
                    'latitude' => 10.3157,
                    'longitude' => 123.8854,
                    'services' => ['consultation', 'vaccination', 'surgery', 'emergency', 'laboratory'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Rosa Villamor',
                            'license_number' => 'VET-2024-010',
                            'specialization' => 'Tropical Medicine'
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
                'email' => 'contact@davaoanimalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Antonio',
                    'last_name' => 'Magbanua',
                    'phone' => '09281234512',
                    'date_of_birth' => '1976-04-20',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Leading veterinarian in Mindanao with expertise in large animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Davao Animal Hospital',
                    'phone' => '082-345-6789',
                    'country' => 'Philippines',
                    'region' => 'Davao Region',
                    'province' => 'Davao del Sur',
                    'city' => 'Davao City',
                    'barangay' => 'Poblacion District',
                    'street_address' => '789 McArthur Highway',
                    'postal_code' => '8000',
                    'latitude' => 7.0644,
                    'longitude' => 125.6078,
                    'services' => ['consultation', 'vaccination', 'surgery', 'emergency', 'boarding'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Antonio Magbanua',
                            'license_number' => 'VET-2024-011',
                            'specialization' => 'Large Animal Medicine'
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
                'email' => 'admin@baguiopetcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Carmen',
                    'last_name' => 'Albano',
                    'phone' => '09391234513',
                    'date_of_birth' => '1981-08-10',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Mountain region veterinarian specializing in cold climate animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Baguio Pet Care Center',
                    'phone' => '074-456-7890',
                    'country' => 'Philippines',
                    'region' => 'Cordillera Administrative Region',
                    'province' => 'Benguet',
                    'city' => 'Baguio',
                    'barangay' => 'Session Road',
                    'street_address' => '321 Session Road',
                    'postal_code' => '2600',
                    'latitude' => 16.4023,
                    'longitude' => 120.5960,
                    'services' => ['consultation', 'vaccination', 'grooming', 'boarding', 'wellness'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Carmen Albano',
                            'license_number' => 'VET-2024-012',
                            'specialization' => 'Cold Climate Medicine'
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
                'email' => 'info@iloilopetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Rafael',
                    'last_name' => 'Villanueva',
                    'phone' => '09451234514',
                    'date_of_birth' => '1974-12-03',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Western Visayas veterinary expert with focus on aquatic animal health.',
                ],
                'clinic' => [
                    'clinic_name' => 'Iloilo Pet Clinic',
                    'phone' => '033-567-8901',
                    'country' => 'Philippines',
                    'region' => 'Western Visayas',
                    'province' => 'Iloilo',
                    'city' => 'Iloilo City',
                    'barangay' => 'Jaro',
                    'street_address' => '654 Gen. Luna Street',
                    'postal_code' => '5000',
                    'latitude' => 10.6996,
                    'longitude' => 122.5547,
                    'services' => ['consultation', 'vaccination', 'surgery', 'aquatic', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Rafael Villanueva',
                            'license_number' => 'VET-2024-013',
                            'specialization' => 'Aquatic Animal Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '06:00', 'close' => '22:00'],
                        'tuesday' => ['open' => '06:00', 'close' => '22:00'],
                        'wednesday' => ['open' => '06:00', 'close' => '22:00'],
                        'thursday' => ['open' => '06:00', 'close' => '22:00'],
                        'friday' => ['open' => '06:00', 'close' => '22:00'],
                        'saturday' => ['open' => '08:00', 'close' => '20:00'],
                        'sunday' => ['open' => '08:00', 'close' => '20:00']
                    ]
                ]
            ],
            [
                'email' => 'contact@cagayanvetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Isabel',
                    'last_name' => 'Morales',
                    'phone' => '09561234515',
                    'date_of_birth' => '1983-06-18',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Northern Luzon veterinarian specializing in rural animal healthcare.',
                ],
                'clinic' => [
                    'clinic_name' => 'Cagayan Veterinary Center',
                    'phone' => '078-678-9012',
                    'country' => 'Philippines',
                    'region' => 'Cagayan Valley',
                    'province' => 'Cagayan',
                    'city' => 'Tuguegarao',
                    'barangay' => 'Centro',
                    'street_address' => '987 Bonifacio Street',
                    'postal_code' => '3500',
                    'latitude' => 17.6132,
                    'longitude' => 121.7270,
                    'services' => ['consultation', 'vaccination', 'surgery', 'livestock', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Isabel Morales',
                            'license_number' => 'VET-2024-014',
                            'specialization' => 'Rural Animal Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '10:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '10:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '10:00', 'close' => '19:00'],
                        'thursday' => ['open' => '10:00', 'close' => '19:00'],
                        'friday' => ['open' => '10:00', 'close' => '19:00'],
                        'saturday' => ['open' => '09:00', 'close' => '18:00'],
                        'sunday' => ['open' => '09:00', 'close' => '18:00']
                    ]
                ]
            ],
            [
                'email' => 'admin@palawanpetcare.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Francisco',
                    'last_name' => 'Delgado',
                    'phone' => '09671234516',
                    'date_of_birth' => '1977-11-25',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Island veterinarian expert in tropical and marine animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Palawan Pet Care',
                    'phone' => '048-789-0123',
                    'country' => 'Philippines',
                    'region' => 'MIMAROPA',
                    'province' => 'Palawan',
                    'city' => 'Puerto Princesa',
                    'barangay' => 'Barangay San Pedro',
                    'street_address' => '147 Rizal Avenue',
                    'postal_code' => '5300',
                    'latitude' => 9.7392,
                    'longitude' => 118.7353,
                    'services' => ['consultation', 'vaccination', 'marine', 'wildlife', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Francisco Delgado',
                            'license_number' => 'VET-2024-015',
                            'specialization' => 'Marine Animal Medicine'
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
                'email' => 'info@batangasvetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Victoria',
                    'last_name' => 'Castillo',
                    'phone' => '09781234517',
                    'date_of_birth' => '1980-02-14',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Southern Luzon veterinarian with expertise in farm animal medicine.',
                ],
                'clinic' => [
                    'clinic_name' => 'Batangas Veterinary Clinic',
                    'phone' => '043-890-1234',
                    'country' => 'Philippines',
                    'region' => 'CALABARZON',
                    'province' => 'Batangas',
                    'city' => 'Batangas City',
                    'barangay' => 'Poblacion',
                    'street_address' => '258 P. Burgos Street',
                    'postal_code' => '4200',
                    'latitude' => 13.7565,
                    'longitude' => 121.0583,
                    'services' => ['consultation', 'vaccination', 'surgery', 'farm animals', 'laboratory'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Victoria Castillo',
                            'license_number' => 'VET-2024-016',
                            'specialization' => 'Farm Animal Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '07:00', 'close' => '18:00'],
                        'tuesday' => ['open' => '07:00', 'close' => '18:00'],
                        'wednesday' => ['open' => '07:00', 'close' => '18:00'],
                        'thursday' => ['open' => '07:00', 'close' => '18:00'],
                        'friday' => ['open' => '07:00', 'close' => '18:00'],
                        'saturday' => ['open' => '08:00', 'close' => '16:00'],
                        'sunday' => ['open' => 'closed', 'close' => 'closed']
                    ]
                ]
            ],
            [
                'email' => 'contact@bicolvetcenter.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Ernesto',
                    'last_name' => 'Miranda',
                    'phone' => '09891234518',
                    'date_of_birth' => '1978-09-07',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Bicol region veterinarian specializing in volcanic region animal health.',
                ],
                'clinic' => [
                    'clinic_name' => 'Bicol Veterinary Center',
                    'phone' => '054-901-2345',
                    'country' => 'Philippines',
                    'region' => 'Bicol Region',
                    'province' => 'Albay',
                    'city' => 'Legazpi',
                    'barangay' => 'Albay District',
                    'street_address' => '369 Penaranda Street',
                    'postal_code' => '4500',
                    'latitude' => 13.1391,
                    'longitude' => 123.7436,
                    'services' => ['consultation', 'vaccination', 'surgery', 'emergency', 'wellness'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Ernesto Miranda',
                            'license_number' => 'VET-2024-017',
                            'specialization' => 'Volcanic Region Medicine'
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
                'email' => 'admin@zambalesanimalhospital.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Melissa',
                    'last_name' => 'Ocampo',
                    'phone' => '09901234519',
                    'date_of_birth' => '1982-05-30',
                    'gender' => 'female',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Central Luzon veterinarian with coastal animal care expertise.',
                ],
                'clinic' => [
                    'clinic_name' => 'Zambales Animal Hospital',
                    'phone' => '047-012-3456',
                    'country' => 'Philippines',
                    'region' => 'Central Luzon',
                    'province' => 'Zambales',
                    'city' => 'Olongapo',
                    'barangay' => 'East Bajac-Bajac',
                    'street_address' => '741 Magsaysay Drive',
                    'postal_code' => '2200',
                    'latitude' => 14.8294,
                    'longitude' => 120.2824,
                    'services' => ['consultation', 'vaccination', 'surgery', 'coastal medicine', 'boarding'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Melissa Ocampo',
                            'license_number' => 'VET-2024-018',
                            'specialization' => 'Coastal Animal Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '06:00', 'close' => '19:00'],
                        'tuesday' => ['open' => '06:00', 'close' => '19:00'],
                        'wednesday' => ['open' => '06:00', 'close' => '19:00'],
                        'thursday' => ['open' => '06:00', 'close' => '19:00'],
                        'friday' => ['open' => '06:00', 'close' => '19:00'],
                        'saturday' => ['open' => '08:00', 'close' => '17:00'],
                        'sunday' => ['open' => '08:00', 'close' => '17:00']
                    ]
                ]
            ],
            [
                'email' => 'info@panayvetclinic.ph',
                'password' => 'clinic123',
                'profile' => [
                    'first_name' => 'Dr. Leonardo',
                    'last_name' => 'Aquino',
                    'phone' => '09111234520',
                    'date_of_birth' => '1975-03-12',
                    'gender' => 'male',
                    'occupation' => 'Veterinarian',
                    'bio' => 'Panay Island veterinarian expert in island ecosystem animal care.',
                ],
                'clinic' => [
                    'clinic_name' => 'Panay Veterinary Clinic',
                    'phone' => '036-123-4567',
                    'country' => 'Philippines',
                    'region' => 'Western Visayas',
                    'province' => 'Aklan',
                    'city' => 'Kalibo',
                    'barangay' => 'Poblacion',
                    'street_address' => '852 Pastrana Street',
                    'postal_code' => '5600',
                    'latitude' => 11.7043,
                    'longitude' => 122.3678,
                    'services' => ['consultation', 'vaccination', 'surgery', 'island medicine', 'emergency'],
                    'veterinarians' => [
                        [
                            'name' => 'Dr. Leonardo Aquino',
                            'license_number' => 'VET-2024-019',
                            'specialization' => 'Island Ecosystem Medicine'
                        ]
                    ],
                    'operating_hours' => [
                        'monday' => ['open' => '10:00', 'close' => '20:00'],
                        'tuesday' => ['open' => '10:00', 'close' => '20:00'],
                        'wednesday' => ['open' => '10:00', 'close' => '20:00'],
                        'thursday' => ['open' => '10:00', 'close' => '20:00'],
                        'friday' => ['open' => '10:00', 'close' => '20:00'],
                        'saturday' => ['open' => '09:00', 'close' => '18:00'],
                        'sunday' => ['open' => '09:00', 'close' => '18:00']
                    ]
                ]
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 CLINIC ACCOUNTS:');
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

        $this->command->info('✅ Clinic accounts seeded successfully!');
    }
}
