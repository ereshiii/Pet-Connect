<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PetBreed;

class PetBreedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $breeds = [
            // Dogs
            ['name' => 'Labrador Retriever', 'species' => 'dog', 'description' => 'Friendly and outgoing'],
            ['name' => 'German Shepherd', 'species' => 'dog', 'description' => 'Loyal and intelligent'],
            ['name' => 'Golden Retriever', 'species' => 'dog', 'description' => 'Intelligent and friendly'],
            ['name' => 'French Bulldog', 'species' => 'dog', 'description' => 'Playful and adaptable'],
            ['name' => 'Bulldog', 'species' => 'dog', 'description' => 'Calm and courageous'],
            ['name' => 'Poodle', 'species' => 'dog', 'description' => 'Intelligent and active'],
            ['name' => 'Beagle', 'species' => 'dog', 'description' => 'Merry and friendly'],
            ['name' => 'Rottweiler', 'species' => 'dog', 'description' => 'Loyal and confident'],
            ['name' => 'Yorkshire Terrier', 'species' => 'dog', 'description' => 'Affectionate and sprightly'],
            ['name' => 'Dachshund', 'species' => 'dog', 'description' => 'Clever and lively'],
            ['name' => 'Shih Tzu', 'species' => 'dog', 'description' => 'Affectionate and playful'],
            ['name' => 'Siberian Husky', 'species' => 'dog', 'description' => 'Loyal and outgoing'],
            ['name' => 'Chihuahua', 'species' => 'dog', 'description' => 'Charming and sassy'],
            ['name' => 'Mixed Breed', 'species' => 'dog', 'description' => 'Unique combination'],
            
            // Cats
            ['name' => 'Persian', 'species' => 'cat', 'description' => 'Sweet and gentle'],
            ['name' => 'Maine Coon', 'species' => 'cat', 'description' => 'Gentle giant'],
            ['name' => 'Siamese', 'species' => 'cat', 'description' => 'Vocal and social'],
            ['name' => 'British Shorthair', 'species' => 'cat', 'description' => 'Easygoing and calm'],
            ['name' => 'Scottish Fold', 'species' => 'cat', 'description' => 'Sweet-tempered'],
            ['name' => 'Ragdoll', 'species' => 'cat', 'description' => 'Docile and placid'],
            ['name' => 'Bengal', 'species' => 'cat', 'description' => 'Active and playful'],
            ['name' => 'Sphynx', 'species' => 'cat', 'description' => 'Energetic and loyal'],
            ['name' => 'Domestic Shorthair', 'species' => 'cat', 'description' => 'Most common house cat'],
            ['name' => 'Domestic Longhair', 'species' => 'cat', 'description' => 'Fluffy companion'],
            
            // Birds
            ['name' => 'Budgerigar', 'species' => 'bird', 'description' => 'Small and social'],
            ['name' => 'Cockatiel', 'species' => 'bird', 'description' => 'Gentle and affectionate'],
            ['name' => 'Lovebird', 'species' => 'bird', 'description' => 'Playful and energetic'],
            ['name' => 'African Grey Parrot', 'species' => 'bird', 'description' => 'Highly intelligent'],
            ['name' => 'Canary', 'species' => 'bird', 'description' => 'Beautiful singers'],
            
            // Rabbits
            ['name' => 'Holland Lop', 'species' => 'rabbit', 'description' => 'Friendly and calm'],
            ['name' => 'Netherland Dwarf', 'species' => 'rabbit', 'description' => 'Small and energetic'],
            ['name' => 'Lionhead', 'species' => 'rabbit', 'description' => 'Friendly and playful'],
            ['name' => 'Mini Rex', 'species' => 'rabbit', 'description' => 'Calm and friendly'],
            
            // Hamsters
            ['name' => 'Syrian Hamster', 'species' => 'hamster', 'description' => 'Solitary and friendly'],
            ['name' => 'Dwarf Hamster', 'species' => 'hamster', 'description' => 'Small and social'],
            
            // Guinea Pigs
            ['name' => 'American Guinea Pig', 'species' => 'guinea_pig', 'description' => 'Short-haired and gentle'],
            ['name' => 'Peruvian Guinea Pig', 'species' => 'guinea_pig', 'description' => 'Long-haired and affectionate'],
            
            // Fish
            ['name' => 'Goldfish', 'species' => 'fish', 'description' => 'Hardy and popular'],
            ['name' => 'Betta Fish', 'species' => 'fish', 'description' => 'Colorful and territorial'],
            ['name' => 'Guppy', 'species' => 'fish', 'description' => 'Peaceful and colorful'],
            
            // Reptiles
            ['name' => 'Bearded Dragon', 'species' => 'reptile', 'description' => 'Docile and friendly'],
            ['name' => 'Leopard Gecko', 'species' => 'reptile', 'description' => 'Easy to care for'],
            ['name' => 'Ball Python', 'species' => 'reptile', 'description' => 'Gentle and calm'],
        ];

        foreach ($breeds as $breed) {
            PetBreed::firstOrCreate(
                ['name' => $breed['name'], 'species' => $breed['species']],
                $breed
            );
        }
    }
}
