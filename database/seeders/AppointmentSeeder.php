<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pet;
use App\Models\Appointment;
use App\Models\ClinicRegistration;
use App\Models\ClinicService;
use App\Models\ClinicStaff;
use Illuminate\Support\Str;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📅 Seeding appointments...');

        // Get necessary data
        $users = User::where('account_type', 'user')->get();
        $pets = Pet::with('owner')->get();
        $clinics = ClinicRegistration::where('status', 'approved')->get();

        if ($users->isEmpty() || $pets->isEmpty() || $clinics->isEmpty()) {
            $this->command->warn('⚠️  Missing required data. Ensure UserSeeder, PetSeeder, and ClinicSeeder have run.');
            return;
        }

        $statuses = ['completed', 'completed', 'completed', 'confirmed', 'pending']; // More completed for reviews
        $reasons = [
            'Regular checkup',
            'Vaccination',
            'Follow-up consultation',
            'Dental cleaning',
            'Grooming session',
            'Skin condition',
            'Wellness exam',
            'Behavior consultation',
            'Weight management',
            'General consultation',
        ];

        $appointmentsCreated = 0;

        // Create 3-5 appointments per pet
        foreach ($pets as $pet) {
            $numAppointments = rand(3, 5);
            $clinic = $clinics->random();
            $services = ClinicService::where('clinic_id', $clinic->id)->get();
            $staff = ClinicStaff::where('clinic_id', $clinic->id)->where('role', 'veterinarian')->first();

            if ($services->isEmpty()) {
                continue;
            }

            for ($i = 0; $i < $numAppointments; $i++) {
                $service = $services->random();
                $status = $statuses[array_rand($statuses)];
                
                // Determine appointment date based on status
                if ($status === 'completed') {
                    // Past appointments (1-60 days ago)
                    $scheduledAt = now()->subDays(rand(1, 60))->setHour(rand(9, 17))->setMinute([0, 15, 30, 45][rand(0, 3)]);
                    $checkedInAt = $scheduledAt->copy()->addMinutes(rand(-5, 10));
                    $checkedOutAt = $checkedInAt->copy()->addMinutes($service->duration_minutes ?? 30);
                } elseif ($status === 'confirmed') {
                    // Near future appointments (1-14 days)
                    $scheduledAt = now()->addDays(rand(1, 14))->setHour(rand(9, 17))->setMinute([0, 15, 30, 45][rand(0, 3)]);
                    $checkedInAt = null;
                    $checkedOutAt = null;
                } else {
                    // Pending appointments (15-30 days)
                    $scheduledAt = now()->addDays(rand(15, 30))->setHour(rand(9, 17))->setMinute([0, 15, 30, 45][rand(0, 3)]);
                    $checkedInAt = null;
                    $checkedOutAt = null;
                }

                $appointment = Appointment::create([
                    'appointment_number' => 'APT-' . strtoupper(Str::random(8)),
                    'pet_id' => $pet->id,
                    'owner_id' => $pet->owner_id,
                    'clinic_id' => $clinic->id,
                    'service_id' => $service->id,
                    'clinic_staff_id' => $staff?->id,
                    'scheduled_at' => $scheduledAt,
                    'duration_minutes' => $service->duration_minutes ?? 30,
                    'type' => 'regular',
                    'priority' => 'normal',
                    'status' => $status,
                    'reason' => $reasons[array_rand($reasons)],
                    'notes' => $status === 'completed' ? 'Appointment completed successfully' : null,
                    'checked_in_at' => $checkedInAt,
                    'checked_out_at' => $checkedOutAt,
                    'created_by' => $pet->owner_id,
                    'is_priority' => false,
                ]);

                $statusEmoji = match($status) {
                    'completed' => '✅',
                    'confirmed' => '📋',
                    'pending' => '⏳',
                    default => '📅'
                };

                $this->command->line("  {$statusEmoji} {$pet->name} @ {$clinic->clinic_name} - {$scheduledAt->format('M d, Y')} ({$status})");
                $appointmentsCreated++;
            }
        }

        $this->command->info("✅ {$appointmentsCreated} appointments seeded successfully!");
    }
}
