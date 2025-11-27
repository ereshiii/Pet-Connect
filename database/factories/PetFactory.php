<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $species = fake()->randomElement(['dog', 'cat', 'bird', 'rabbit', 'hamster']);
        $breeds = [
            'dog' => ['Labrador', 'Golden Retriever', 'German Shepherd', 'Beagle', 'Bulldog', 'Poodle', 'Shih Tzu', 'Aspin'],
            'cat' => ['Persian', 'Siamese', 'Maine Coon', 'British Shorthair', 'Puspin', 'Tabby'],
            'bird' => ['Parrot', 'Cockatiel', 'Canary', 'Lovebird', 'Maya'],
            'rabbit' => ['Holland Lop', 'Flemish Giant', 'Angora'],
            'hamster' => ['Syrian', 'Dwarf', 'Roborovski'],
        ];

        return [
            'owner_id' => User::factory(),
            'name' => fake()->firstName(),
            'species' => $species,
            'breed' => fake()->randomElement($breeds[$species]),
            'gender' => fake()->randomElement(['male', 'female']),
            'birth_date' => fake()->dateTimeBetween('-10 years', '-2 months'),
            'weight' => fake()->randomFloat(2, 1, 50),
            'size' => fake()->randomElement(['small', 'medium', 'large']),
            'color' => fake()->randomElement(['brown', 'black', 'white', 'golden', 'gray', 'spotted']),
            'markings' => fake()->optional()->sentence(),
            'microchip_number' => fake()->optional()->numerify('############'),
            'is_neutered' => fake()->boolean(),
            'special_needs' => fake()->optional()->sentence(),
            'notes' => fake()->optional()->paragraph(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the pet is a puppy.
     */
    public function puppy(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => fake()->dateTimeBetween('-1 year', '-2 months'),
            'species' => 'dog',
        ]);
    }

    /**
     * Indicate that the pet is a kitten.
     */
    public function kitten(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => fake()->dateTimeBetween('-1 year', '-2 months'),
            'species' => 'cat',
        ]);
    }

    /**
     * Indicate that the pet is senior.
     */
    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => fake()->dateTimeBetween('-15 years', '-8 years'),
        ]);
    }
}
