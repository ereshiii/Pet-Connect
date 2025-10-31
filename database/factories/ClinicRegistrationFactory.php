<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClinicRegistration>
 */
class ClinicRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clinic_name' => $this->faker->company . ' Veterinary Clinic',
            'clinic_description' => $this->faker->paragraph,
            'website' => $this->faker->optional()->url,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+639' . $this->faker->randomNumber(9, true),
            'country' => 'Philippines',
            'region' => $this->faker->randomElement(['NCR', 'Region I', 'Region II', 'Region III', 'Region IV-A']),
            'province' => $this->faker->randomElement(['Metro Manila', 'Laguna', 'Cavite', 'Rizal', 'Bulacan']),
            'city' => $this->faker->city,
            'barangay' => 'Barangay ' . $this->faker->randomNumber(2),
            'street_address' => $this->faker->streetAddress,
            'postal_code' => $this->faker->postcode,
            'latitude' => $this->faker->latitude(14.0, 15.0), // Philippines latitude range
            'longitude' => $this->faker->longitude(120.0, 122.0), // Philippines longitude range
            'rating' => $this->faker->randomFloat(1, 3.0, 5.0),
            'total_reviews' => $this->faker->numberBetween(0, 500),
            'is_featured' => $this->faker->boolean(10), // 10% chance
            'is_open_24_7' => $this->faker->boolean(20), // 20% chance
            'is_24_hours' => $this->faker->boolean(20), // 20% chance
            'operating_hours' => [
                'monday' => ['open' => '08:00', 'close' => '18:00'],
                'tuesday' => ['open' => '08:00', 'close' => '18:00'],
                'wednesday' => ['open' => '08:00', 'close' => '18:00'],
                'thursday' => ['open' => '08:00', 'close' => '18:00'],
                'friday' => ['open' => '08:00', 'close' => '18:00'],
                'saturday' => ['open' => '08:00', 'close' => '16:00'],
                'sunday' => ['open' => '09:00', 'close' => '15:00'],
            ],
            'services' => $this->faker->randomElements([
                'General Consultation',
                'Vaccination',
                'Surgery',
                'Dental Care',
                'Grooming',
                'Emergency Care',
                'X-Ray',
                'Laboratory Tests',
                'Pet Boarding',
                'Microchipping'
            ], $this->faker->numberBetween(3, 7)),
            'other_services' => $this->faker->optional()->sentence,
            'veterinarians' => [
                [
                    'name' => 'Dr. ' . $this->faker->firstName . ' ' . $this->faker->lastName,
                    'license' => 'VET-' . $this->faker->randomNumber(6, true),
                    'specialization' => $this->faker->randomElement(['General Practice', 'Surgery', 'Internal Medicine', 'Dermatology']),
                ],
                [
                    'name' => 'Dr. ' . $this->faker->firstName . ' ' . $this->faker->lastName,
                    'license' => 'VET-' . $this->faker->randomNumber(6, true),
                    'specialization' => $this->faker->randomElement(['General Practice', 'Surgery', 'Internal Medicine', 'Dermatology']),
                ]
            ],
            'certification_proofs' => [
                'veterinary_license.pdf',
                'business_permit.pdf',
                'sanitary_permit.pdf'
            ],
            'additional_info' => $this->faker->optional()->paragraph,
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'rejection_reason' => null,
            'submitted_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'approved_at' => null,
            'approved_by' => null,
        ];
    }

    /**
     * Indicate that the clinic registration is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the clinic registration is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'approved_by' => User::factory(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the clinic registration is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
            'rejection_reason' => $this->faker->sentence,
        ]);
    }

    /**
     * Indicate that the clinic registration is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'rejection_reason' => $this->faker->sentence,
        ]);
    }
}
