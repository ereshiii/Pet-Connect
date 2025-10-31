<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pet;
use App\Models\PetBreed;
use App\Models\PetType;
use App\Models\PetMedicalRecord;
use App\Models\PetVaccination;
use App\Models\PetHealthCondition;
use App\Models\User;
use Carbon\Carbon;

class PetManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First create pet types
        $petTypes = [
            ['name' => 'Domestic', 'species' => 'dog', 'description' => 'Common household dogs'],
            ['name' => 'Domestic', 'species' => 'cat', 'description' => 'Common household cats'],
            ['name' => 'Working', 'species' => 'dog', 'description' => 'Working dogs'],
            ['name' => 'Show', 'species' => 'dog', 'description' => 'Show or competition dogs'],
            ['name' => 'Show', 'species' => 'cat', 'description' => 'Show or competition cats'],
        ];

        foreach ($petTypes as $type) {
            PetType::firstOrCreate(['name' => $type['name'], 'species' => $type['species']], $type);
        }

        // Create pet breeds
        $petBreeds = [
            // Dog breeds
            [
                'name' => 'Golden Retriever',
                'species' => 'dog',
                'description' => 'A large-sized gun dog that retrieves shot waterfowl during hunting.',
                'characteristics' => [
                    'size_category' => 'large',
                    'temperament' => 'Friendly, intelligent, devoted',
                    'life_expectancy_min' => 10,
                    'life_expectancy_max' => 12,
                    'weight_range_min' => 25.0,
                    'weight_range_max' => 34.0,
                    'height_range_min' => 51.0,
                    'height_range_max' => 61.0,
                    'common_health_issues' => ['Hip dysplasia', 'Heart disease', 'Eye conditions'],
                    'grooming_requirements' => 'Regular brushing, seasonal shedding',
                    'exercise_requirements' => 'High - daily walks and playtime',
                ],
            ],
            [
                'name' => 'German Shepherd',
                'species' => 'dog',
                'description' => 'A working dog breed known for their loyalty and versatility.',
                'characteristics' => [
                    'size_category' => 'large',
                    'temperament' => 'Confident, courageous, smart',
                    'life_expectancy_min' => 9,
                    'life_expectancy_max' => 13,
                    'weight_range_min' => 22.0,
                    'weight_range_max' => 40.0,
                    'height_range_min' => 55.0,
                    'height_range_max' => 65.0,
                    'common_health_issues' => ['Hip dysplasia', 'Elbow dysplasia', 'Bloat'],
                    'grooming_requirements' => 'Regular brushing, heavy shedding',
                    'exercise_requirements' => 'High - needs job or purpose',
                ],
            ],
            [
                'name' => 'Labrador Retriever',
                'species' => 'dog',
                'description' => 'A friendly and outgoing retriever breed.',
                'characteristics' => [
                    'size_category' => 'large',
                    'temperament' => 'Outgoing, active, friendly',
                    'life_expectancy_min' => 10,
                    'life_expectancy_max' => 12,
                    'weight_range_min' => 25.0,
                    'weight_range_max' => 36.0,
                    'height_range_min' => 54.0,
                    'height_range_max' => 62.0,
                    'common_health_issues' => ['Hip dysplasia', 'Elbow dysplasia', 'Eye conditions'],
                    'grooming_requirements' => 'Weekly brushing, seasonal shedding',
                    'exercise_requirements' => 'High - daily exercise needed',
                ],
            ],
            [
                'name' => 'French Bulldog',
                'species' => 'dog',
                'description' => 'A small domestic dog breed known for their bat ears.',
                'characteristics' => [
                    'size_category' => 'small',
                    'temperament' => 'Playful, smart, adaptable',
                    'life_expectancy_min' => 10,
                    'life_expectancy_max' => 12,
                    'weight_range_min' => 8.0,
                    'weight_range_max' => 13.0,
                    'height_range_min' => 28.0,
                    'height_range_max' => 33.0,
                    'common_health_issues' => ['Breathing problems', 'Hip dysplasia', 'Eye conditions'],
                    'grooming_requirements' => 'Minimal - weekly brushing',
                    'exercise_requirements' => 'Low to moderate',
                ],
            ],
            // Cat breeds
            [
                'name' => 'British Shorthair',
                'species' => 'cat',
                'description' => 'A pedigreed version of the traditional British domestic cat.',
                'characteristics' => [
                    'size_category' => 'medium',
                    'temperament' => 'Calm, easy-going, affectionate',
                    'life_expectancy_min' => 12,
                    'life_expectancy_max' => 17,
                    'weight_range_min' => 3.2,
                    'weight_range_max' => 7.7,
                    'height_range_min' => 12.0,
                    'height_range_max' => 14.0,
                    'common_health_issues' => ['Hypertrophic cardiomyopathy', 'Hemophilia B'],
                    'grooming_requirements' => 'Weekly brushing',
                    'exercise_requirements' => 'Moderate - indoor play',
                ],
            ],
            [
                'name' => 'Maine Coon',
                'species' => 'cat',
                'description' => 'One of the largest domesticated cat breeds.',
                'characteristics' => [
                    'size_category' => 'large',
                    'temperament' => 'Gentle, friendly, intelligent',
                    'life_expectancy_min' => 13,
                    'life_expectancy_max' => 14,
                    'weight_range_min' => 4.5,
                    'weight_range_max' => 8.2,
                    'height_range_min' => 25.0,
                    'height_range_max' => 40.0,
                    'common_health_issues' => ['Hip dysplasia', 'Hypertrophic cardiomyopathy'],
                    'grooming_requirements' => 'Daily brushing needed',
                    'exercise_requirements' => 'Moderate to high',
                ],
            ],
            [
                'name' => 'Persian',
                'species' => 'cat',
                'description' => 'A long-haired breed known for their round faces.',
                'characteristics' => [
                    'size_category' => 'medium',
                    'temperament' => 'Quiet, sweet, gentle',
                    'life_expectancy_min' => 12,
                    'life_expectancy_max' => 17,
                    'weight_range_min' => 3.2,
                    'weight_range_max' => 5.5,
                    'height_range_min' => 10.0,
                    'height_range_max' => 15.0,
                    'common_health_issues' => ['Breathing problems', 'Eye conditions', 'Kidney disease'],
                    'grooming_requirements' => 'Daily brushing required',
                    'exercise_requirements' => 'Low - gentle play',
                ],
            ],
            [
                'name' => 'Siamese',
                'species' => 'cat',
                'description' => 'A breed known for their pointed coloration and blue eyes.',
                'characteristics' => [
                    'size_category' => 'medium',
                    'temperament' => 'Active, vocal, social',
                    'life_expectancy_min' => 13,
                    'life_expectancy_max' => 15,
                    'weight_range_min' => 2.3,
                    'weight_range_max' => 4.5,
                    'height_range_min' => 20.0,
                    'height_range_max' => 25.0,
                    'common_health_issues' => ['Respiratory issues', 'Dental problems'],
                    'grooming_requirements' => 'Minimal - weekly brushing',
                    'exercise_requirements' => 'High - active play needed',
                ],
            ],
            // Mixed breeds
            [
                'name' => 'Mixed Breed',
                'species' => 'dog',
                'description' => 'A mixed breed dog with varied characteristics.',
                'characteristics' => [
                    'size_category' => 'medium',
                    'temperament' => 'Varies',
                    'life_expectancy_min' => 10,
                    'life_expectancy_max' => 15,
                    'weight_range_min' => 10.0,
                    'weight_range_max' => 30.0,
                    'height_range_min' => 30.0,
                    'height_range_max' => 60.0,
                    'common_health_issues' => ['Varies by mix'],
                    'grooming_requirements' => 'Varies by coat type',
                    'exercise_requirements' => 'Moderate to high',
                ],
            ],
            [
                'name' => 'Mixed Breed',
                'species' => 'cat',
                'description' => 'A mixed breed cat with varied characteristics.',
                'characteristics' => [
                    'size_category' => 'medium',
                    'temperament' => 'Varies',
                    'life_expectancy_min' => 13,
                    'life_expectancy_max' => 17,
                    'weight_range_min' => 3.0,
                    'weight_range_max' => 6.0,
                    'height_range_min' => 15.0,
                    'height_range_max' => 25.0,
                    'common_health_issues' => ['Varies by mix'],
                    'grooming_requirements' => 'Varies by coat type',
                    'exercise_requirements' => 'Moderate',
                ],
            ],
        ];

        foreach ($petBreeds as $breed) {
            PetBreed::firstOrCreate(['name' => $breed['name'], 'species' => $breed['species']], $breed);
        }

        // Get the first user (pet owner) - adjust this as needed
        $user = User::where('account_type', 'user')->first();
        
        if (!$user) {
            $this->command->warn('No user found. Creating a test user for pets...');
            $user = User::create([
                'name' => 'Pet Owner Test User',
                'username' => 'petowner',
                'email' => 'petowner@test.com',
                'password' => bcrypt('password'),
                'account_type' => 'user',
                'email_verified_at' => now(),
            ]);
        }

        // Get breeds and types for pet creation
        $goldenRetriever = PetBreed::where('name', 'Golden Retriever')->where('species', 'dog')->first();
        $germanShepherd = PetBreed::where('name', 'German Shepherd')->where('species', 'dog')->first();
        $britishShorthair = PetBreed::where('name', 'British Shorthair')->where('species', 'cat')->first();
        $maineCoon = PetBreed::where('name', 'Maine Coon')->where('species', 'cat')->first();
        $mixedDog = PetBreed::where('name', 'Mixed Breed')->where('species', 'dog')->first();
        
        $domesticDogType = PetType::where('name', 'Domestic')->where('species', 'dog')->first();
        $domesticCatType = PetType::where('name', 'Domestic')->where('species', 'cat')->first();

        // Create sample pets
        $pets = [
            [
                'owner_id' => $user->id,
                'name' => 'Bella',
                'species' => 'dog',
                'breed_id' => $goldenRetriever->id,
                'type_id' => $domesticDogType->id,
                'gender' => 'female',
                'birth_date' => Carbon::now()->subYears(3)->subMonths(2),
                'weight' => 28.5,
                'size' => 'large',
                'color' => 'Golden',
                'markings' => 'White chest patch',
                'microchip_number' => '982000123456789',
                'is_neutered' => true,
                'special_needs' => null,
                'notes' => 'Very friendly and loves to swim. Excellent with children.',
                'is_active' => true,
            ],
            [
                'owner_id' => $user->id,
                'name' => 'Max',
                'species' => 'dog',
                'breed_id' => $germanShepherd->id,
                'type_id' => $domesticDogType->id,
                'gender' => 'male',
                'birth_date' => Carbon::now()->subYears(5)->subMonths(8),
                'weight' => 35.2,
                'size' => 'large',
                'color' => 'Black and Tan',
                'markings' => 'Black saddle pattern',
                'microchip_number' => '982000123456790',
                'is_neutered' => true,
                'special_needs' => 'Requires joint supplements for mild hip dysplasia',
                'notes' => 'Well-trained guard dog, protective of family.',
                'is_active' => true,
            ],
            [
                'owner_id' => $user->id,
                'name' => 'Luna',
                'species' => 'cat',
                'breed_id' => $britishShorthair->id,
                'type_id' => $domesticCatType->id,
                'gender' => 'female',
                'birth_date' => Carbon::now()->subYears(2)->subMonths(1),
                'weight' => 4.8,
                'size' => 'medium',
                'color' => 'Blue (Gray)',
                'markings' => 'Solid color',
                'microchip_number' => '982000123456791',
                'is_neutered' => true,
                'special_needs' => null,
                'notes' => 'Indoor cat, very calm and affectionate.',
                'is_active' => true,
            ],
            [
                'owner_id' => $user->id,
                'name' => 'Charlie',
                'species' => 'cat',
                'breed_id' => $maineCoon->id,
                'type_id' => $domesticCatType->id,
                'gender' => 'male',
                'birth_date' => Carbon::now()->subYears(4)->subMonths(3),
                'weight' => 6.1,
                'size' => 'large',
                'color' => 'Brown Tabby',
                'markings' => 'Classic tabby pattern with white bib',
                'microchip_number' => '982000123456792',
                'is_neutered' => true,
                'special_needs' => null,
                'notes' => 'Large and gentle, loves to climb cat trees.',
                'is_active' => true,
            ],
            [
                'owner_id' => $user->id,
                'name' => 'Rocky',
                'species' => 'dog',
                'breed_id' => $mixedDog->id,
                'type_id' => $domesticDogType->id,
                'gender' => 'male',
                'birth_date' => Carbon::now()->subYears(1)->subMonths(6),
                'weight' => 15.3,
                'size' => 'medium',
                'color' => 'Brindle',
                'markings' => 'White paws and chest',
                'microchip_number' => '982000123456793',
                'is_neutered' => false,
                'special_needs' => null,
                'notes' => 'Energetic puppy, still in training.',
                'is_active' => true,
            ],
        ];

        foreach ($pets as $petData) {
            $pet = Pet::firstOrCreate(['name' => $petData['name'], 'owner_id' => $petData['owner_id']], $petData);

            // Add medical records for each pet
            $this->createMedicalRecord($pet);
            
            // Add vaccinations for each pet
            $this->createVaccinations($pet);
            
            // Add health conditions for some pets
            if (in_array($pet->name, ['Max', 'Luna'])) {
                $this->createHealthConditions($pet);
            }
        }

        $this->command->info('Pet management data seeded successfully!');
    }

    private function createMedicalRecord($pet)
    {
        PetMedicalRecord::firstOrCreate([
            'pet_id' => $pet->id,
            'date' => Carbon::now()->subMonths(2),
        ], [
            'record_type' => 'checkup',
            'title' => 'Annual Wellness Examination',
            'description' => 'Overall excellent health. Heart and lungs clear. No abnormalities detected. Weight: ' . $pet->weight . ' kg. Temperature: 38.5°C. Heart rate: ' . ($pet->species === 'dog' ? '100' : '180') . ' bpm.',
            'cost' => 85.00,
            'instructions' => 'Continue current diet and exercise routine. Return in 1 year for annual checkup.',
            'follow_up_date' => Carbon::now()->addMonths(10),
        ]);
    }

    private function createVaccinations($pet)
    {
        $vaccinations = [];
        
        if ($pet->species === 'dog') {
            $vaccinations = [
                [
                    'vaccine_name' => 'DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)',
                    'administered_date' => Carbon::now()->subMonths(8),
                    'expiry_date' => Carbon::now()->addMonths(4),
                ],
                [
                    'vaccine_name' => 'Rabies',
                    'administered_date' => Carbon::now()->subMonths(10),
                    'expiry_date' => Carbon::now()->addMonths(14),
                ],
                [
                    'vaccine_name' => 'Bordetella (Kennel Cough)',
                    'administered_date' => Carbon::now()->subMonths(5),
                    'expiry_date' => Carbon::now()->addMonths(7),
                ],
            ];
        } else { // cat
            $vaccinations = [
                [
                    'vaccine_name' => 'FVRCP (Feline Viral Rhinotracheitis, Calicivirus, Panleukopenia)',
                    'administered_date' => Carbon::now()->subMonths(8),
                    'expiry_date' => Carbon::now()->addMonths(4),
                ],
                [
                    'vaccine_name' => 'Rabies',
                    'administered_date' => Carbon::now()->subMonths(10),
                    'expiry_date' => Carbon::now()->addMonths(14),
                ],
            ];
        }

        foreach ($vaccinations as $vaccination) {
            PetVaccination::firstOrCreate([
                'pet_id' => $pet->id,
                'vaccine_name' => $vaccination['vaccine_name'],
            ], array_merge($vaccination, [
                'notes' => 'No adverse reactions observed',
            ]));
        }
    }

    private function createHealthConditions($pet)
    {
        if ($pet->name === 'Max') {
            PetHealthCondition::firstOrCreate([
                'pet_id' => $pet->id,
                'name' => 'Hip Dysplasia',
            ], [
                'type' => 'condition',
                'description' => 'Mild hip dysplasia diagnosed through X-ray examination',
                'severity' => 'mild',
                'diagnosed_date' => Carbon::now()->subMonths(6),
                'treatment' => 'Weight management, joint supplements, controlled exercise. Swimming recommended for low-impact exercise.',
                'is_active' => true,
            ]);
        }

        if ($pet->name === 'Luna') {
            PetHealthCondition::firstOrCreate([
                'pet_id' => $pet->id,
                'name' => 'Seasonal Allergies',
            ], [
                'type' => 'allergy',
                'description' => 'Seasonal environmental allergies causing skin irritation',
                'severity' => 'mild',
                'diagnosed_date' => Carbon::now()->subMonths(4),
                'treatment' => 'Antihistamines during allergy season, hypoallergenic diet. Monitor for excessive scratching during spring and fall.',
                'is_active' => true,
            ]);
        }
    }
}