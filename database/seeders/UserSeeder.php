<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding regular users...');
        
        $users = [
            [
                'email' => 'juan.cruz@gmail.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Juan',
                    'last_name' => 'Cruz',
                    'phone' => '09171234567',
                    'date_of_birth' => '1990-05-15',
                    'gender' => 'male',
                    'occupation' => 'Software Engineer',
                    'bio' => 'Love animals, especially dogs. Currently have 2 golden retrievers.',
                ],
                'address' => [
                    'address_line_1' => '123 Taft Avenue',
                    'address_line_2' => 'Unit 5B',
                    'city' => 'Manila',
                    'state' => 'Metro Manila',
                    'postal_code' => '1000',
                    'latitude' => 14.5995,
                    'longitude' => 120.9842,
                ]
            ],
            [
                'email' => 'maria.santos@yahoo.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Maria',
                    'last_name' => 'Santos',
                    'phone' => '09281234567',
                    'date_of_birth' => '1985-08-22',
                    'gender' => 'female',
                    'occupation' => 'Teacher',
                    'bio' => 'Dedicated teacher and cat lover. Rescues stray cats in her free time.',
                ],
                'address' => [
                    'address_line_1' => '456 EDSA',
                    'city' => 'Quezon City',
                    'state' => 'Metro Manila',
                    'postal_code' => '1100',
                    'latitude' => 14.6760,
                    'longitude' => 121.0437,
                ]
            ],
            [
                'email' => 'jose.reyes@outlook.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Jose',
                    'last_name' => 'Reyes',
                    'phone' => '09391234567',
                    'date_of_birth' => '1988-12-03',
                    'gender' => 'male',
                    'occupation' => 'Business Owner',
                    'bio' => 'Small business owner who loves birds. Has a colorful collection of parrots.',
                ],
                'address' => [
                    'address_line_1' => '789 Rizal Street',
                    'city' => 'Makati',
                    'state' => 'Metro Manila',
                    'postal_code' => '1200',
                    'latitude' => 14.5547,
                    'longitude' => 121.0244,
                ]
            ],
            [
                'email' => 'ana.garcia@gmail.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Ana',
                    'last_name' => 'Garcia',
                    'phone' => '09451234567',
                    'date_of_birth' => '1992-03-18',
                    'gender' => 'female',
                    'occupation' => 'Nurse',
                    'bio' => 'Healthcare professional with a passion for animal welfare.',
                ],
                'address' => [
                    'address_line_1' => '321 Bonifacio Avenue',
                    'city' => 'Pasig',
                    'state' => 'Metro Manila',
                    'postal_code' => '1600',
                    'latitude' => 14.5764,
                    'longitude' => 121.0851,
                ]
            ],
            [
                'email' => 'carlos.mendoza@yahoo.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Carlos',
                    'last_name' => 'Mendoza',
                    'phone' => '09561234567',
                    'date_of_birth' => '1987-07-09',
                    'gender' => 'male',
                    'occupation' => 'Architect',
                    'bio' => 'Creative professional who designs pet-friendly spaces.',
                ],
                'address' => [
                    'address_line_1' => '654 Ortigas Avenue',
                    'city' => 'Mandaluyong',
                    'state' => 'Metro Manila',
                    'postal_code' => '1550',
                    'latitude' => 14.5776,
                    'longitude' => 121.0436,
                ]
            ],
            [
                'email' => 'luz.fernandez@gmail.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Luz',
                    'last_name' => 'Fernandez',
                    'phone' => '09671234567',
                    'date_of_birth' => '1983-11-25',
                    'gender' => 'female',
                    'occupation' => 'Marketing Manager',
                    'bio' => 'Marketing professional and proud pet parent to 3 rescue dogs.',
                ],
                'address' => [
                    'address_line_1' => '987 Commonwealth Avenue',
                    'city' => 'Quezon City',
                    'state' => 'Metro Manila',
                    'postal_code' => '1121',
                    'latitude' => 14.6507,
                    'longitude' => 121.0318,
                ]
            ],
            [
                'email' => 'ricardo.torres@outlook.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Ricardo',
                    'last_name' => 'Torres',
                    'phone' => '09781234567',
                    'date_of_birth' => '1991-01-14',
                    'gender' => 'male',
                    'occupation' => 'IT Specialist',
                    'bio' => 'Tech enthusiast who loves gadgets and his pet hamster.',
                ],
                'address' => [
                    'address_line_1' => '147 Katipunan Avenue',
                    'city' => 'Quezon City',
                    'state' => 'Metro Manila',
                    'postal_code' => '1108',
                    'latitude' => 14.6365,
                    'longitude' => 121.0703,
                ]
            ],
            [
                'email' => 'elena.valdez@gmail.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Elena',
                    'last_name' => 'Valdez',
                    'phone' => '09891234567',
                    'date_of_birth' => '1989-09-30',
                    'gender' => 'female',
                    'occupation' => 'Veterinary Assistant',
                    'bio' => 'Veterinary assistant passionate about animal health and wellness.',
                ],
                'address' => [
                    'address_line_1' => '258 Aurora Boulevard',
                    'city' => 'San Juan',
                    'state' => 'Metro Manila',
                    'postal_code' => '1500',
                    'latitude' => 14.6019,
                    'longitude' => 121.0355,
                ]
            ],
            [
                'email' => 'miguel.ramos@yahoo.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Miguel',
                    'last_name' => 'Ramos',
                    'phone' => '09901234567',
                    'date_of_birth' => '1986-04-12',
                    'gender' => 'male',
                    'occupation' => 'Chef',
                    'bio' => 'Professional chef who cooks healthy meals for his pets too.',
                ],
                'address' => [
                    'address_line_1' => '369 Shaw Boulevard',
                    'city' => 'Pasig',
                    'state' => 'Metro Manila',
                    'postal_code' => '1600',
                    'latitude' => 14.5863,
                    'longitude' => 121.0581,
                ]
            ],
            [
                'email' => 'sofia.herrera@gmail.com',
                'password' => 'password123',
                'profile' => [
                    'first_name' => 'Sofia',
                    'last_name' => 'Herrera',
                    'phone' => '09121234567',
                    'date_of_birth' => '1994-06-28',
                    'gender' => 'female',
                    'occupation' => 'Graphic Designer',
                    'bio' => 'Creative designer who loves illustrating animals and owns 2 cats.',
                ],
                'address' => [
                    'address_line_1' => '741 Jupiter Street',
                    'city' => 'Makati',
                    'state' => 'Metro Manila',
                    'postal_code' => '1209',
                    'latitude' => 14.5631,
                    'longitude' => 121.0161,
                ]
            ]
        ];

        $this->command->info('');
        $this->command->info('🔐 REGULAR USER ACCOUNTS:');
        $this->command->info('====================================');

        foreach ($users as $userData) {
            $this->command->line("Seeding user: {$userData['email']}");

            // Create or update user (idempotent)
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'password' => Hash::make($userData['password']),
                    'account_type' => 'user',
                    'is_admin' => false,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Create or update profile
            if (method_exists($user, 'profile')) {
                if (!$user->profile) {
                    $user->profile()->create($userData['profile']);
                } else {
                    $user->profile->update($userData['profile']);
                }
            }

            // Create or update address (match by city + postal_code when possible)
            if (method_exists($user, 'addresses') && isset($userData['address'])) {
                $addressData = array_merge($userData['address'], [
                    'type' => 'home',
                    'is_primary' => true,
                ]);

                $query = $user->addresses();
                if (!empty($userData['address']['city'])) {
                    $query = $query->where('city', $userData['address']['city']);
                }
                if (!empty($userData['address']['postal_code'])) {
                    $query = $query->where('postal_code', $userData['address']['postal_code']);
                }

                $existingAddress = $query->first();
                if ($existingAddress) {
                    $existingAddress->update($addressData);
                } else {
                    $user->addresses()->create($addressData);
                }
            }

            // Create emergency contact if missing
            if (method_exists($user, 'emergencyContacts')) {
                $contactName = 'Emergency Contact for ' . ($userData['profile']['first_name'] ?? $user->name ?? 'User');
                $existingContact = $user->emergencyContacts()->where('name', $contactName)->first();
                if (!$existingContact) {
                    $user->emergencyContacts()->create([
                        'name' => $contactName,
                        'relationship' => 'friend',
                        'phone' => '09' . mt_rand(100000000, 999999999),
                        'is_primary' => true,
                    ]);
                }
            }

            $this->command->line("📧 {$userData['email']} | 🔑 {$userData['password']} | 👤 {$userData['profile']['first_name']} {$userData['profile']['last_name']}");
        }

        $this->command->info('✅ Regular users seeded successfully!');
    }
}
