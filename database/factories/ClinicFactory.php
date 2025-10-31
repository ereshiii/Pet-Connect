<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clinic>
 */
class ClinicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Veterinary Clinic',
            'license_number' => 'VET-' . $this->faker->randomNumber(6, true),
            'type' => $this->faker->randomElement(['general', 'specialty', 'emergency']),
            'description' => $this->faker->paragraph,
            'services' => $this->faker->randomElements([
                'General Consultation',
                'Vaccination',
                'Surgery',
                'Dental Care',
                'Emergency Care',
                'Laboratory Tests',
                'X-Ray',
                'Grooming'
            ], $this->faker->numberBetween(3, 6)),
            'specialties' => $this->faker->randomElements([
                'Cardiology',
                'Dermatology',
                'Orthopedics',
                'Oncology',
                'Internal Medicine',
                'Surgery'
            ], $this->faker->numberBetween(1, 3)),
            'phone' => '+639' . $this->faker->randomNumber(9, true),
            'email' => $this->faker->unique()->safeEmail,
            'website' => $this->faker->optional()->url,
            'social_media' => [
                'facebook' => $this->faker->optional()->url,
                'instagram' => $this->faker->optional()->userName,
            ],
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'average_rating' => $this->faker->randomFloat(1, 3.0, 5.0),
            'total_reviews' => $this->faker->numberBetween(0, 500),
            'last_review_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the clinic is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the clinic is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the clinic has emergency services.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'services' => array_merge($attributes['services'] ?? [], ['Emergency Care']),
            'type' => 'emergency',
        ]);
    }

    /**
     * Indicate that the clinic is open 24 hours.
     */
    public function open24Hours(): static
    {
        return $this->state(fn (array $attributes) => [
            'services' => array_merge($attributes['services'] ?? [], ['24/7 Available']),
        ]);
    }
}
