<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'appointment_reminder',
            'appointment_confirmed',
            'appointment_cancelled',
            'payment_received',
            'review_received',
            'system_announcement',
        ];

        $type = fake()->randomElement($types);

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'title' => fake()->sentence(4),
            'message' => fake()->sentence(10),
            'data' => json_encode([
                'action_url' => fake()->optional()->url(),
                'related_id' => fake()->optional()->randomNumber(),
            ]),
            'is_read' => fake()->boolean(30), // 30% chance of being read
            'read_at' => fake()->optional(0.3)->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create an appointment reminder notification.
     */
    public function appointmentReminder(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'appointment_reminder',
            'title' => 'Appointment Reminder',
            'message' => 'You have an appointment tomorrow at ' . fake()->time('g:i A'),
        ]);
    }
}
