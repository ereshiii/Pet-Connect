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
        $this->command->info('📅 Seeding appointments for demo user...');

        // Get demo user and their pets
        $user = User::where('email', 'demo@petconnect.com')->first();
        
        if (!$user) {
            $this->command->warn('⚠️  Demo user not found. Please run UserSeeder first.');
            return;
        }

        $pets = Pet::where('owner_id', $user->id)->get();
        $clinics = ClinicRegistration::where('status', 'approved')->get();

        if ($pets->isEmpty()) {
            $this->command->warn('⚠️  No pets found for demo user. Please run PetSeeder first.');
            return;
        }

        if ($clinics->isEmpty()) {
            $this->command->warn('⚠️  No clinics found. Please run ClinicSeeder first.');
            return;
        }

        $statuses = ['completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'confirmed', 'pending']; // 75% completed for more reviews and medical records
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
            'Spay/Neuter surgery',
            'Emergency visit',
            'Flea and tick treatment',
            'Allergy testing',
            'Blood work',
            'X-ray imaging',
            'Microchipping',
            'Health certificate',
            'Nail trimming',
            'Ear cleaning',
        ];

        $appointmentsCreated = 0;

        // Create 200-210 appointments per pet across different clinics (1000+ total for 5 pets)
        foreach ($pets as $pet) {
            $numAppointments = rand(200, 210);
            
            for ($i = 0; $i < $numAppointments; $i++) {
                // Use different clinics for variety
                $clinic = $clinics->random();
                $services = ClinicService::where('clinic_id', $clinic->id)->get();
                $staff = ClinicStaff::where('clinic_id', $clinic->id)->where('role', 'veterinarian')->first();

                if ($services->isEmpty()) {
                    continue;
                }

                $service = $services->random();
                $status = $statuses[array_rand($statuses)];
                
                // Determine appointment date based on status
                if ($status === 'completed') {
                    // Past appointments (1-180 days ago for richer history - 6 months)
                    $scheduledAt = now()->subDays(rand(1, 180))->setHour(rand(9, 17))->setMinute([0, 15, 30, 45][rand(0, 3)]);
                    $checkedInAt = $scheduledAt->copy()->addMinutes(rand(-5, 10));
                    $checkedOutAt = $checkedInAt->copy()->addMinutes($service->duration_minutes ?? 30);
                } elseif ($status === 'confirmed') {
                    // Near future appointments (1-30 days)
                    $scheduledAt = now()->addDays(rand(1, 30))->setHour(rand(9, 17))->setMinute([0, 15, 30, 45][rand(0, 3)]);
                    $checkedInAt = null;
                    $checkedOutAt = null;
                } else {
                    // Pending appointments (31-60 days)
                    $scheduledAt = now()->addDays(rand(31, 60))->setHour(rand(9, 17))->setMinute([0, 15, 30, 45][rand(0, 3)]);
                    $checkedInAt = null;
                    $checkedOutAt = null;
                }

                $appointment = Appointment::create([
                    'appointment_number' => 'APT-' . strtoupper(Str::random(8)),
                    'pet_id' => $pet->id,
                    'owner_id' => $user->id,
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
                    'created_by' => $user->id,
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

        $this->command->info("✅ {$appointmentsCreated} appointments seeded successfully for demo user!");
    }
}
