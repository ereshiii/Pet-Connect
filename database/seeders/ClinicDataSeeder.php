<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\ClinicAddress;
use App\Models\ClinicOperatingHour;
use App\Models\ClinicStaff;
use App\Models\ClinicService;
use App\Models\ClinicReview;
use App\Models\User;

class ClinicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample clinic (or get existing one)
        $clinic = Clinic::firstOrCreate(
            ['license_number' => 'VET-2024-001'],
            [
                'registration_id' => null, // Can be null for direct clinic creation
                'name' => 'Happy Paws Veterinary Clinic',
                'type' => 'general',
                'description' => 'Happy Paws Veterinary is a full-service animal hospital providing comprehensive veterinary care for dogs, cats, and exotic pets. Our experienced team of veterinarians and support staff are dedicated to providing the highest quality care with compassion and understanding.',
                'services' => ['consultation', 'vaccination', 'surgery', 'dental', 'emergency'],
                'specialties' => ['small_animals', 'exotic_pets'],
                'phone' => '09123456789',
                'email' => 'info@happypawsvet.com',
                'website' => 'www.happypawsvet.com',
                'social_media' => [
                    'facebook' => 'https://facebook.com/happypawsvet',
                    'instagram' => 'https://instagram.com/happypawsvet'
                ],
                'status' => 'active',
                'average_rating' => 4.8,
                'total_reviews' => 15,
                'last_review_at' => now()->subDays(2),
            ]
        );

        // Create clinic address (if it doesn't exist)
        if (!$clinic->addresses()->exists()) {
            ClinicAddress::create([
                'clinic_id' => $clinic->id,
                'type' => 'main',
                'address_line_1' => '789 Elm Street',
                'address_line_2' => 'Barangay Westside',
                'city' => 'Quezon City',
                'state' => 'Metro Manila',
                'postal_code' => '1100',
                'country' => 'Philippines',
                'latitude' => 14.6760,
                'longitude' => 121.0437,
                'is_primary' => true,
            ]);
        }

        // Create operating hours (if they don't exist)
        if (!$clinic->operatingHours()->exists()) {
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            foreach ($days as $day) {
                ClinicOperatingHour::create([
                    'clinic_id' => $clinic->id,
                    'day_of_week' => $day,
                    'opening_time' => '08:00:00',
                    'closing_time' => '18:00:00',
                    'is_closed' => false,
                ]);
            }

            // Saturday - shorter hours
            ClinicOperatingHour::create([
                'clinic_id' => $clinic->id,
                'day_of_week' => 'saturday',
                'opening_time' => '09:00:00',
                'closing_time' => '16:00:00',
                'is_closed' => false,
            ]);

            // Sunday - emergency only
            ClinicOperatingHour::create([
                'clinic_id' => $clinic->id,
                'day_of_week' => 'sunday',
                'opening_time' => '10:00:00',
                'closing_time' => '14:00:00',
                'is_closed' => false,
                'notes' => 'Emergency services only',
            ]);
        }

        // Create sample users for staff (if they don't exist)
        $drSarah = User::firstOrCreate(
            ['email' => 'dr.sarah@happypawsvet.com'],
            [
                'name' => 'Dr. Sarah Johnson',
                'username' => 'dr_sarah_johnson',
                'password' => bcrypt('password'),
                'account_type' => 'clinic',
            ]
        );

        $drMichael = User::firstOrCreate(
            ['email' => 'dr.michael@happypawsvet.com'],
            [
                'name' => 'Dr. Michael Chen',
                'username' => 'dr_michael_chen', 
                'password' => bcrypt('password'),
                'account_type' => 'clinic',
            ]
        );

        $lisa = User::firstOrCreate(
            ['email' => 'lisa@happypawsvet.com'],
            [
                'name' => 'Lisa Rodriguez',
                'username' => 'lisa_rodriguez',
                'password' => bcrypt('password'), 
                'account_type' => 'clinic',
            ]
        );

        // Create staff members (if they don't exist)
        if (!$clinic->staff()->exists()) {
            ClinicStaff::create([
                'clinic_id' => $clinic->id,
                'user_id' => $drSarah->id,
                'role' => 'veterinarian',
                'license_number' => 'VET-12345',
                'specializations' => ['Internal Medicine', 'Surgery'],
                'start_date' => now()->subYears(15),
                'is_active' => true,
            ]);

            ClinicStaff::create([
                'clinic_id' => $clinic->id,
                'user_id' => $drMichael->id,
                'role' => 'veterinarian',
                'license_number' => 'VET-12346',
                'specializations' => ['Exotic Pets', 'Dental Care'],
                'start_date' => now()->subYears(8),
                'is_active' => true,
            ]);

            ClinicStaff::create([
                'clinic_id' => $clinic->id,
                'user_id' => $lisa->id,
                'role' => 'assistant',
                'specializations' => ['Emergency Care', 'Laboratory'],
                'start_date' => now()->subYears(12),
                'is_active' => true,
            ]);
        }

        // Create services (if they don't exist)
        if (!$clinic->clinicServices()->exists()) {
        $services = [
            [
                'name' => 'General Health Examination',
                'category' => 'consultation',
                'description' => 'Comprehensive health check-up for your pet including physical examination and health assessment.',
                'base_price' => 1500.00,
                'duration_minutes' => 30,
                'requires_appointment' => true,
            ],
            [
                'name' => 'Vaccination Program',
                'category' => 'vaccination',
                'description' => 'Complete vaccination schedule for puppies, kittens, and adult pets.',
                'base_price' => 800.00,
                'duration_minutes' => 15,
                'requires_appointment' => true,
            ],
            [
                'name' => 'Surgical Procedures',
                'category' => 'surgery',
                'description' => 'Various surgical procedures including spaying, neutering, and emergency surgeries.',
                'base_price' => 5000.00,
                'duration_minutes' => 120,
                'requires_appointment' => true,
            ],
            [
                'name' => 'Dental Care',
                'category' => 'dental',
                'description' => 'Professional dental cleaning, tooth extractions, and oral health maintenance.',
                'base_price' => 3000.00,
                'duration_minutes' => 90,
                'requires_appointment' => true,
            ],
            [
                'name' => 'Emergency Care',
                'category' => 'emergency',
                'description' => '24/7 emergency veterinary services for urgent pet health issues.',
                'base_price' => 2500.00,
                'duration_minutes' => 60,
                'is_emergency_service' => true,
                'requires_appointment' => false,
            ],
            [
                'name' => 'Laboratory Services',
                'category' => 'diagnostic',
                'description' => 'Blood tests, urinalysis, and other diagnostic laboratory services.',
                'base_price' => 1200.00,
                'duration_minutes' => 20,
                'requires_appointment' => true,
            ],
            [
                'name' => 'Pet Grooming',
                'category' => 'grooming',
                'description' => 'Professional pet grooming services including bathing, nail trimming, and styling.',
                'base_price' => 800.00,
                'duration_minutes' => 60,
                'requires_appointment' => true,
            ],
        ];

            foreach ($services as $service) {
                ClinicService::create(array_merge($service, [
                    'clinic_id' => $clinic->id,
                    'is_active' => true,
                ]));
            }
        }

        // Create sample reviews (if we have users and no reviews exist)
        if (!$clinic->reviews()->exists()) {
        $users = User::limit(5)->get();
        if ($users->count() > 0) {
            $reviews = [
                [
                    'rating' => 5,
                    'comment' => 'Excellent care for my dog Max. The staff is very professional and caring. Dr. Johnson explained everything clearly and my pet felt comfortable throughout the visit.',
                ],
                [
                    'rating' => 5,
                    'comment' => 'Best veterinary clinic in the area! They handled my cat emergency surgery perfectly. Highly recommend Happy Paws to all pet owners.',
                ],
                [
                    'rating' => 4,
                    'comment' => 'Great service and clean facilities. The only downside is sometimes the wait can be a bit long, but the quality of care makes it worth it.',
                ],
                [
                    'rating' => 5,
                    'comment' => 'Dr. Chen is amazing with exotic pets! My rabbit was treated with such care and expertise. Will definitely come back.',
                ],
                [
                    'rating' => 5,
                    'comment' => 'Emergency service was quick and efficient. Lisa, the vet tech, was so helpful during a scary situation with my dog.',
                ],
            ];

            foreach ($reviews as $index => $review) {
                if (isset($users[$index])) {
                    ClinicReview::create(array_merge($review, [
                        'clinic_id' => $clinic->id,
                        'user_id' => $users[$index]->id,
                        'is_verified' => true,
                        'helpful_votes' => $index < 3 ? [1, 2, 3] : [], // Some reviews have helpful votes
                    ]));
                }
            }
        }
        }

        $this->command->info('Sample clinic data created successfully!');
        $this->command->info("Clinic ID: {$clinic->id} - {$clinic->name}");
    }
}
