<?php

namespace Database\Factories;

use App\Models\PetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PetBreed>
 */
class PetBreedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $breeds = [
            'Labrador Retriever',
            'Golden Retriever',
            'German Shepherd',
            'Persian Cat',
            'Siamese Cat',
            'Beagle',
            'Bulldog',
            'Poodle',
            'Shih Tzu',
            'Aspin',
            'Puspin',
        ];

        return [
            'name' => fake()->unique()->randomElement($breeds),
            'type_id' => PetType::factory(),
            'description' => fake()->optional()->sentence(),
            'size_range' => fake()->randomElement(['small', 'medium', 'large']),
            'temperament' => fake()->optional()->words(3, true),
        ];
    }
}
