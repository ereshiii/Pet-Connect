<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\ClinicRegistration;
use App\Models\ClinicService;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a clinic
        $clinicUser = User::where('account_type', 'clinic')->first();
        if (!$clinicUser) {
            $this->command->info('No clinic user found. Please run ClinicUserSeeder first.');
            return;
        }

        $clinicRegistration = ClinicRegistration::where('user_id', $clinicUser->id)->first();
        if (!$clinicRegistration) {
            $this->command->info('No clinic registration found.');
            return;
        }

        // Get an existing pet
        $pet = Pet::first();
        if (!$pet) {
            $this->command->info('No pets found. Please create some pets first.');
            return;
        }

        // Get the pet owner
        $petOwner = $pet->owner;
        if (!$petOwner) {
            $this->command->info('Pet has no owner.');
            return;
        }

        // Get a clinic service
        $service = ClinicService::where('clinic_id', $clinicRegistration->id)->first();

        // Create appointments for today and upcoming days
        $appointments = [
            [
                'scheduled_at' => Carbon::today()->setTime(9, 0),
                'status' => 'scheduled',
                'actual_cost' => 500.00,
            ],
            [
                'scheduled_at' => Carbon::today()->setTime(14, 0),
                'status' => 'completed',
                'actual_cost' => 1200.00,
            ],
            [
                'scheduled_at' => Carbon::today()->addDay()->setTime(10, 0),
                'status' => 'scheduled',
                'estimated_cost' => 800.00,
            ],
            [
                'scheduled_at' => Carbon::today()->addDays(2)->setTime(15, 30),
                'status' => 'confirmed',
                'estimated_cost' => 2500.00,
            ],
        ];

        foreach ($appointments as $appointmentData) {
            Appointment::create([
                'appointment_number' => 'APT-' . Carbon::today()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'pet_id' => $pet->id,
                'owner_id' => $petOwner->id,
                'clinic_id' => $clinicRegistration->id,
                'service_id' => $service ? $service->id : null,
                'scheduled_at' => $appointmentData['scheduled_at'],
                'duration_minutes' => 30,
                'type' => 'consultation',
                'priority' => 'normal',
                'status' => $appointmentData['status'],
                'reason' => 'Regular checkup and consultation',
                'estimated_cost' => $appointmentData['estimated_cost'] ?? null,
                'actual_cost' => $appointmentData['actual_cost'] ?? null,
                'created_by' => $petOwner->id,
            ]);
        }

        $this->command->info('Created ' . count($appointments) . ' test appointments.');
    }
}