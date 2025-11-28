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
        $this->command->info('🐾 Seeding pets for demo user...');

        // Get the demo user
        $user = User::where('email', 'demo@petconnect.com')->first();

        if (!$user) {
            $this->command->warn('⚠️  Demo user not found. Please run UserSeeder first.');
            return;
        }

        // Get pet types and breeds
        $dogType = PetType::where('name', 'Dog')->first();
        $catType = PetType::where('name', 'Cat')->first();

        // Dog breeds
        $goldenRetriever = PetBreed::where('name', 'Golden Retriever')->first();
        $labrador = PetBreed::where('name', 'Labrador Retriever')->first();
        $beagle = PetBreed::where('name', 'Beagle')->first();

        // Cat breeds
        $persian = PetBreed::where('name', 'Persian')->first();
        $siamese = PetBreed::where('name', 'Siamese')->first();

        // Create 5 pets for demo user (mix of dogs and cats)
        $petData = [
            [
                'name' => 'Max',
                'species' => 'Dog',
                'type_id' => $dogType?->id,
                'breed_id' => $goldenRetriever?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(3)->subMonths(6)->format('Y-m-d'),
                'weight' => 30.5,
                'color' => 'Golden',
                'is_neutered' => true,
                'special_needs' => 'Very friendly and loves to play fetch',
            ],
            [
                'name' => 'Bella',
                'species' => 'Cat',
                'type_id' => $catType?->id,
                'breed_id' => $persian?->id,
                'gender' => 'female',
                'birth_date' => now()->subYears(2)->format('Y-m-d'),
                'weight' => 4.5,
                'color' => 'White',
                'is_neutered' => true,
                'special_needs' => 'Indoor cat, very calm',
            ],
            [
                'name' => 'Charlie',
                'species' => 'Dog',
                'type_id' => $dogType?->id,
                'breed_id' => $labrador?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(4)->format('Y-m-d'),
                'weight' => 32.0,
                'color' => 'Chocolate',
                'is_neutered' => true,
                'special_needs' => 'Very energetic, needs lots of exercise',
            ],
            [
                'name' => 'Luna',
                'species' => 'Cat',
                'type_id' => $catType?->id,
                'breed_id' => $siamese?->id,
                'gender' => 'female',
                'birth_date' => now()->subYears(1)->format('Y-m-d'),
                'weight' => 3.8,
                'color' => 'Seal Point',
                'is_neutered' => false,
                'special_needs' => 'Very vocal and active',
            ],
            [
                'name' => 'Rocky',
                'species' => 'Dog',
                'type_id' => $dogType?->id,
                'breed_id' => $beagle?->id,
                'gender' => 'male',
                'birth_date' => now()->subYears(5)->format('Y-m-d'),
                'weight' => 12.5,
                'color' => 'Tricolor',
                'is_neutered' => true,
                'special_needs' => 'Loves food, needs weight management',
            ],
        ];

        $petsCreated = 0;
        
        foreach ($petData as $data) {
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

            $this->command->line("  🐾 Created: {$data['name']} for {$user->email}");
            $petsCreated++;
        }

        $this->command->info("✅ {$petsCreated} pets seeded successfully for demo user!");
    }
}
