<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PetType;
use Illuminate\Support\Facades\DB;

class PetTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $petTypes = [
            ['name' => 'Dog', 'species' => 'dog', 'description' => 'Canine companions'],
            ['name' => 'Cat', 'species' => 'cat', 'description' => 'Feline friends'],
            ['name' => 'Bird', 'species' => 'bird', 'description' => 'Avian pets'],
            ['name' => 'Rabbit', 'species' => 'rabbit', 'description' => 'Small mammals'],
            ['name' => 'Hamster', 'species' => 'hamster', 'description' => 'Pocket pets'],
            ['name' => 'Guinea Pig', 'species' => 'guinea_pig', 'description' => 'Cavies'],
            ['name' => 'Fish', 'species' => 'fish', 'description' => 'Aquatic pets'],
            ['name' => 'Reptile', 'species' => 'reptile', 'description' => 'Reptilian pets'],
            ['name' => 'Other', 'species' => 'other', 'description' => 'Other types of pets'],
        ];

        foreach ($petTypes as $type) {
            PetType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
