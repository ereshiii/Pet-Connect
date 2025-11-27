<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClinicRegistration;
use App\Models\ClinicStaff;
use App\Models\ClinicService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
                    'description' => 'Premier pet healthcare facility in Makati offering comprehensive veterinary services with state-of-the-art equipment and experienced veterinarians.',
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
            ],
            // BATCH 3 (merged): 10 clinics
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
            // BATCH 4 (merged): 10 clinics
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
            // BATCH 5 (merged): 5 clinics
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
        $this->command->info('🔐 CLINIC ACCOUNTS:');
        $this->command->info('====================================');

        // find admin user to set as approver (avoid hard-coded id)
        $adminUser = User::where('is_admin', true)->first();
        $approvedById = $adminUser ? $adminUser->id : 1;

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
                [
                    'user_id' => $user->id,
                    'clinic_name' => $clinicData['clinic']['clinic_name'],
                    'clinic_description' => $clinicData['clinic']['description'] ?? null,
                    'email' => $clinicData['email'],
                    'phone' => $clinicData['clinic']['phone'],
                    'country' => $clinicData['clinic']['country'],
                    'region' => $clinicData['clinic']['region'],
                    'province' => $clinicData['clinic']['province'],
                    'city' => $clinicData['clinic']['city'],
                    'barangay' => $clinicData['clinic']['barangay'],
                    'street_address' => $clinicData['clinic']['street_address'],
                    'postal_code' => $clinicData['clinic']['postal_code'],
                    'operating_hours' => $clinicData['clinic']['operating_hours'],
                    'services' => $clinicData['clinic']['services'],
                    'veterinarians' => $clinicData['clinic']['veterinarians'] ?? [],
                    'certification_proofs' => [],
                    'status' => 'approved',
                    'submitted_at' => now(),
                    'approved_at' => now(),
                    'approved_by' => $approvedById,
                ]
            );

            // Create clinic operating hours entries
            foreach ($clinicData['clinic']['operating_hours'] as $day => $hours) {
                $isClosed = ($hours['open'] === 'closed' || $hours['close'] === 'closed');
                
                DB::table('clinic_operating_hours')->updateOrInsert(
                    [
                        'clinic_id' => $registration->id,
                        'day_of_week' => $day
                    ],
                    [
                        'opening_time' => $isClosed ? null : $hours['open'],
                        'closing_time' => $isClosed ? null : $hours['close'],
                        'is_closed' => $isClosed,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Create clinic services
            foreach ($clinicData['clinic']['services'] as $serviceName) {
                DB::table('clinic_services')->updateOrInsert(
                    [
                        'clinic_id' => $registration->id,
                        'name' => ucfirst($serviceName),
                    ],
                    [
                        'description' => "Professional {$serviceName} service",
                        'category' => $serviceName,
                        'duration_minutes' => $this->getServiceDuration($serviceName),
                        'requires_appointment' => true,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Create clinic staff/veterinarians if any
            if (isset($clinicData['clinic']['veterinarians']) && !empty($clinicData['clinic']['veterinarians'])) {
                foreach ($clinicData['clinic']['veterinarians'] as $vetData) {
                    // For seeding, we'll just create the clinic staff entry for the clinic owner
                    // In production, veterinarians would have their own user accounts
                    $ownerName = trim(($clinicData['profile']['first_name'] ?? '') . ' ' . ($clinicData['profile']['last_name'] ?? ''));
                    if (empty($ownerName)) {
                        $ownerName = $clinicData['clinic']['clinic_name'] . ' Owner';
                    }
                    
                    DB::table('clinic_staff')->updateOrInsert(
                        [
                            'clinic_id' => $registration->id,
                            'user_id' => $user->id,
                        ],
                        [
                            'name' => $ownerName,
                            'email' => $user->email,
                            'phone' => $user->phone ?? $clinicData['clinic']['phone'] ?? null,
                            'role' => 'veterinarian',
                            'license_number' => $vetData['license'] ?? 'VET-' . str_pad($registration->id, 6, '0', STR_PAD_LEFT),
                            'specializations' => json_encode([$vetData['specialization'] ?? 'General Practice']),
                            'start_date' => now()->subYear(),
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                    break; // Only create one staff entry for the owner
                }
            } else {
                // Create default staff entry for clinic owner
                $ownerName = trim(($clinicData['profile']['first_name'] ?? '') . ' ' . ($clinicData['profile']['last_name'] ?? ''));
                if (empty($ownerName)) {
                    $ownerName = $clinicData['clinic']['clinic_name'] . ' Owner';
                }
                
                DB::table('clinic_staff')->updateOrInsert(
                    [
                        'clinic_id' => $registration->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'name' => $ownerName,
                        'email' => $user->email,
                        'phone' => $user->phone ?? $clinicData['clinic']['phone'] ?? null,
                        'role' => 'veterinarian',
                        'license_number' => 'VET-' . str_pad($registration->id, 6, '0', STR_PAD_LEFT),
                        'specializations' => json_encode(['General Practice']),
                        'start_date' => now()->subYear(),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $this->command->line("📧 {$clinicData['email']} | 🔑 {$clinicData['password']} | 🏥 {$clinicData['clinic']['clinic_name']}");
        }

        $this->command->info('✅ Clinic accounts seeded successfully!');
    }

    /**
     * Get duration in minutes for a service type.
     */
    private function getServiceDuration(string $service): int
    {
        $durations = [
            'consultation' => 30,
            'vaccination' => 20,
            'surgery' => 120,
            'grooming' => 60,
            'boarding' => 0, // Not time-based
            'emergency' => 45,
            'dental' => 60,
            'wellness' => 45,
            'nutrition' => 30,
            'behavioral' => 60,
            'imaging' => 45,
            'laboratory' => 30,
            'aquatic' => 40,
            'livestock' => 60,
            'marine' => 90,
            'wildlife' => 90,
        ];

        return $durations[$service] ?? 30;
    }
}
