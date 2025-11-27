<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pet;
use App\Models\PetType;
use App\Models\PetBreed;
use Illuminate\Support\Facades\DB;

class PetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🐾 Seeding pets...');

        // Get regular users (not clinics or admins)
        $users = User::where('account_type', 'user')->get();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No regular users found. Please run UserSeeder first.');
            return;
        }

        // Get pet types and breeds
        $dogType = PetType::where('name', 'Dog')->first();
        $catType = PetType::where('name', 'Cat')->first();

        // Dog breeds
        $goldenRetriever = PetBreed::where('name', 'Golden Retriever')->first();
        $labrador = PetBreed::where('name', 'Labrador Retriever')->first();
        $beagle = PetBreed::where('name', 'Beagle')->first();
        $shihtzu = PetBreed::where('name', 'Shih Tzu')->first();
        $poodle = PetBreed::where('name', 'Poodle')->first();
        $bulldog = PetBreed::where('name', 'Bulldog')->first();

        // Cat breeds
        $persian = PetBreed::where('name', 'Persian')->first();
        $siamese = PetBreed::where('name', 'Siamese')->first();
        $maine = PetBreed::where('name', 'Maine Coon')->first();
        $british = PetBreed::where('name', 'British Shorthair')->first();

        $petData = [
            // Juan Cruz - 2 dogs
            [
                'name' => 'Max',
                'species' => 'dog',
                'breed_id' => $goldenRetriever?->id,
                'breed' => 'Golden Retriever',
                'type_id' => $dogType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(3)->subMonths(6)->format('Y-m-d'),
                'weight' => 30.5,
                'color' => 'Golden',
                'is_neutered' => true,
                'notes' => 'Very friendly and loves to play fetch',
            ],
            [
                'name' => 'Bella',
                'species' => 'dog',
                'breed_id' => $goldenRetriever?->id,
                'breed' => 'Golden Retriever',
                'type_id' => $dogType?->id,
                'gender' => 'female',
                'birth_date' => now()->subYears(2)->subMonths(8)->format('Y-m-d'),
                'weight' => 28.0,
                'color' => 'Golden',
                'is_neutered' => true,
                'notes' => 'Gentle and great with kids',
            ],
            // Maria Santos - 3 cats
            [
                'name' => 'Whiskers',
                'species' => 'cat',
                'breed_id' => $persian?->id,
                'breed' => 'Persian',
                'type_id' => $catType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(4)->format('Y-m-d'),
                'weight' => 5.5,
                'color' => 'White',
                'is_neutered' => true,
                'notes' => 'Indoor cat, very calm',
            ],
            [
                'name' => 'Mittens',
                'species' => 'cat',
                'breed_id' => $siamese?->id,
                'breed' => 'Siamese',
                'type_id' => $catType?->id,
                'gender' => 'female',
                'birth_date' => now()->subYears(2)->format('Y-m-d'),
                'weight' => 4.2,
                'color' => 'Cream and Brown',
                'is_neutered' => true,
                'notes' => 'Very vocal and active',
            ],
            [
                'name' => 'Luna',
                'species' => 'cat',
                'breed_id' => $british?->id,
                'breed' => 'British Shorthair',
                'type_id' => $catType?->id,
                'gender' => 'female',
                'birth_date' => now()->subYears(1)->format('Y-m-d'),
                'weight' => 3.8,
                'color' => 'Gray',
                'is_neutered' => false,
                'notes' => 'Rescued stray, still adjusting',
            ],
            // Jose Reyes - 1 dog
            [
                'name' => 'Rocky',
                'species' => 'dog',
                'breed_id' => $beagle?->id,
                'breed' => 'Beagle',
                'type_id' => $dogType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(5)->format('Y-m-d'),
                'weight' => 12.5,
                'color' => 'Tricolor',
                'is_neutered' => true,
                'notes' => 'Loves food, needs weight management',
                'allergies' => 'Chicken',
            ],
            // Ana Garcia - 2 cats
            [
                'name' => 'Shadow',
                'species' => 'cat',
                'breed_id' => $maine?->id,
                'breed' => 'Maine Coon',
                'type_id' => $catType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(3)->format('Y-m-d'),
                'weight' => 7.5,
                'color' => 'Black',
                'is_neutered' => true,
                'notes' => 'Large and gentle giant',
            ],
            [
                'name' => 'Cleo',
                'species' => 'cat',
                'breed_id' => $siamese?->id,
                'breed' => 'Siamese',
                'type_id' => $catType?->id,
                'gender' => 'female',
                'birth_date' => now()->subYears(6)->format('Y-m-d'),
                'weight' => 4.0,
                'color' => 'Seal Point',
                'is_neutered' => true,
                'notes' => 'Senior cat, regular checkups needed',
                'medical_conditions' => 'Mild arthritis',
            ],
            // Carlos Mendoza - 1 dog
            [
                'name' => 'Duke',
                'species' => 'dog',
                'breed_id' => $labrador?->id,
                'breed' => 'Labrador Retriever',
                'type_id' => $dogType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(4)->format('Y-m-d'),
                'weight' => 32.0,
                'color' => 'Chocolate',
                'is_neutered' => true,
                'notes' => 'Very energetic, needs lots of exercise',
            ],
            // Luz Fernandez - 3 dogs
            [
                'name' => 'Charlie',
                'species' => 'dog',
                'breed_id' => $shihtzu?->id,
                'breed' => 'Shih Tzu',
                'type_id' => $dogType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(7)->format('Y-m-d'),
                'weight' => 6.5,
                'color' => 'White and Brown',
                'is_neutered' => true,
                'notes' => 'Rescue dog, very sweet',
            ],
            [
                'name' => 'Daisy',
                'species' => 'dog',
                'breed_id' => $poodle?->id,
                'breed' => 'Poodle',
                'type_id' => $dogType?->id,
                'gender' => 'female',
                'birth_date' => now()->subYears(2)->format('Y-m-d'),
                'weight' => 8.0,
                'color' => 'Apricot',
                'is_neutered' => true,
                'notes' => 'Hypoallergenic, great for family',
            ],
            [
                'name' => 'Buddy',
                'species' => 'dog',
                'breed_id' => $beagle?->id,
                'breed' => 'Beagle',
                'type_id' => $dogType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(1)->subMonths(6)->format('Y-m-d'),
                'weight' => 10.0,
                'color' => 'Tricolor',
                'is_neutered' => false,
                'notes' => 'Young and playful',
            ],
            // Ricardo Torres - 1 cat
            [
                'name' => 'Smokey',
                'species' => 'cat',
                'breed_id' => $british?->id,
                'breed' => 'British Shorthair',
                'type_id' => $catType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(3)->format('Y-m-d'),
                'weight' => 6.0,
                'color' => 'Gray',
                'is_neutered' => true,
                'notes' => 'Indoor cat, lazy but lovable',
            ],
            // Elena Valdez - 2 dogs
            [
                'name' => 'Cooper',
                'species' => 'dog',
                'breed_id' => $labrador?->id,
                'breed' => 'Labrador Retriever',
                'type_id' => $dogType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(2)->format('Y-m-d'),
                'weight' => 28.0,
                'color' => 'Yellow',
                'is_neutered' => true,
                'notes' => 'Service dog in training',
            ],
            [
                'name' => 'Molly',
                'species' => 'dog',
                'breed_id' => $bulldog?->id,
                'breed' => 'Bulldog',
                'type_id' => $dogType?->id,
                'gender' => 'female',
                'birth_date' => now()->subYears(4)->format('Y-m-d'),
                'weight' => 22.0,
                'color' => 'Brindle',
                'is_neutered' => true,
                'notes' => 'Prone to breathing issues, needs monitoring',
                'medical_conditions' => 'Brachycephalic syndrome',
            ],
            // Miguel Ramos - 1 dog
            [
                'name' => 'Zeus',
                'species' => 'dog',
                'breed_id' => $labrador?->id,
                'breed' => 'Labrador Retriever',
                'type_id' => $dogType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(3)->format('Y-m-d'),
                'weight' => 31.0,
                'color' => 'Black',
                'is_neutered' => true,
                'notes' => 'Well-trained and obedient',
            ],
            // Sofia Herrera - 2 cats
            [
                'name' => 'Oliver',
                'species' => 'cat',
                'breed_id' => $persian?->id,
                'breed' => 'Persian',
                'type_id' => $catType?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(2)->format('Y-m-d'),
                'weight' => 5.0,
                'color' => 'Orange',
                'is_neutered' => true,
                'notes' => 'Needs regular grooming',
            ],
            [
                'name' => 'Lily',
                'species' => 'cat',
                'breed_id' => $siamese?->id,
                'breed' => 'Siamese',
                'type_id' => $catType?->id,
                'gender' => 'female',
                'birth_date' => now()->subMonths(10)->format('Y-m-d'),
                'weight' => 3.0,
                'color' => 'Lilac Point',
                'is_neutered' => false,
                'notes' => 'Young kitten, very playful',
            ],
        ];

        $userIndex = 0;
        $petsCreated = 0;
        
        // Distribution: user 0 (2 pets), user 1 (3 pets), user 2 (1 pet), etc.
        $petDistribution = [2, 3, 1, 2, 1, 3, 1, 2, 1, 2]; // Total: 18 pets for 10 users

        foreach ($users as $userIdx => $user) {
            $petsForThisUser = $petDistribution[$userIdx] ?? 1;
            
            for ($i = 0; $i < $petsForThisUser; $i++) {
                if ($userIndex >= count($petData)) break;
                
                $data = $petData[$userIndex];
                $pet = Pet::updateOrCreate(
                    [
                        'owner_id' => $user->id,
                        'name' => $data['name'],
                    ],
                    array_merge($data, [
                        'owner_id' => $user->id,
                        'is_active' => true,
                    ])
                );

                $this->command->line("  🐾 Created: {$data['name']} ({$data['species']}) for {$user->email}");
                $petsCreated++;
                $userIndex++;
            }
        }

        $this->command->info("✅ {$petsCreated} pets seeded successfully!");
    }
}
