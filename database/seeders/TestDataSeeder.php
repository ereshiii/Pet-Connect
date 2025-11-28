<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Pet;
use App\Models\Appointment;
use App\Models\ClinicRegistration;
use App\Models\ClinicService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find or create the pet owner user
        $petOwner = User::where('email', 'mpoke7557@gmail.com')->first();
        
        if (!$petOwner) {
            $petOwner = User::create([
                'email' => 'mpoke7557@gmail.com',
                'password' => Hash::make('password'),
                'account_type' => 'user',
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            // Create user profile
            UserProfile::create([
                'user_id' => $petOwner->id,
                'first_name' => 'Test',
                'last_name' => 'Pet Owner',
                'phone' => '09123456789',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
            ]);
        }

        // Find MM's clinic
        $mmClinic = ClinicRegistration::where('clinic_name', 'like', '%MM%')
            ->where('status', 'approved')
            ->first();

        if (!$mmClinic) {
            $this->command->error("MM's clinic not found. Please ensure the clinic exists in the database.");
            return;
        }

        $this->command->info("Found clinic: {$mmClinic->clinic_name}");

        // Get a service from the clinic
        $service = $mmClinic->clinicServices()->first();
        
        if (!$service) {
            $this->command->error("No services found for {$mmClinic->clinic_name}. Creating a default service.");
            $service = ClinicService::create([
                'clinic_id' => $mmClinic->id,
                'name' => 'General Consultation',
                'description' => 'General veterinary consultation',
                'category' => 'consultation',
                'duration_minutes' => 30,
            ]);
        }

        // Create pets for the pet owner
        $pets = [
            [
                'name' => 'Max',
                'species' => 'dog',
                'breed' => 'Golden Retriever',
                'gender' => 'male',
                'birth_date' => Carbon::now()->subYears(3),
                'weight' => 30.5,
                'color' => 'Golden',
                'is_neutered' => true,
            ],
            [
                'name' => 'Luna',
                'species' => 'cat',
                'breed' => 'Persian',
                'gender' => 'female',
                'birth_date' => Carbon::now()->subYears(2),
                'weight' => 4.2,
                'color' => 'White',
                'is_neutered' => true,
            ],
            [
                'name' => 'Charlie',
                'species' => 'dog',
                'breed' => 'Labrador',
                'gender' => 'male',
                'birth_date' => Carbon::now()->subYears(5),
                'weight' => 28.0,
                'color' => 'Black',
                'is_neutered' => false,
            ],
        ];

        $createdPets = [];
        foreach ($pets as $petData) {
            $pet = Pet::where('owner_id', $petOwner->id)
                ->where('name', $petData['name'])
                ->first();

            if (!$pet) {
                $pet = Pet::create(array_merge($petData, [
                    'owner_id' => $petOwner->id,
                    'is_active' => true,
                ]));
                $this->command->info("Created pet: {$pet->name}");
            }
            $createdPets[] = $pet;
        }

        // Create 10 past appointments
        $this->command->info("\nCreating 10 past appointments...");
        for ($i = 1; $i <= 10; $i++) {
            $scheduledDate = Carbon::now()->subDays(rand(30, 180));
            $pet = $createdPets[array_rand($createdPets)];

            $statuses = ['completed', 'completed', 'completed', 'completed', 'cancelled', 'no_show'];
            $status = $statuses[array_rand($statuses)];

            $appointment = Appointment::create([
                'appointment_number' => 'APT-' . $scheduledDate->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'pet_id' => $pet->id,
                'owner_id' => $petOwner->id,
                'clinic_id' => $mmClinic->id,
                'service_id' => $service->id,
                'scheduled_at' => $scheduledDate,
                'duration_minutes' => 30,
                'type' => 'consultation',
                'status' => $status,
                'reason' => $this->getRandomReason(),
                'notes' => 'Test appointment created by seeder',
                'created_at' => $scheduledDate->copy()->subDays(rand(1, 5)),
            ]);

            if ($status === 'completed') {
                $appointment->update([
                    'checked_in_at' => $scheduledDate->copy()->subMinutes(10),
                    'checked_out_at' => $scheduledDate->copy()->addMinutes(40),
                    'actual_cost' => rand(500, 2000),
                ]);
            }

            $this->command->info("Created past appointment #{$i}: {$appointment->appointment_number} - {$status}");
        }

        // Create 10 future appointments
        $this->command->info("\nCreating 10 future appointments...");
        for ($i = 1; $i <= 10; $i++) {
            $scheduledDate = Carbon::now()->addDays(rand(1, 60));
            $pet = $createdPets[array_rand($createdPets)];

            $statuses = ['scheduled', 'scheduled', 'scheduled', 'confirmed', 'confirmed'];
            $status = $statuses[array_rand($statuses)];

            $appointment = Appointment::create([
                'appointment_number' => 'APT-' . $scheduledDate->format('Ymd') . '-' . str_pad($i + 1000, 4, '0', STR_PAD_LEFT),
                'pet_id' => $pet->id,
                'owner_id' => $petOwner->id,
                'clinic_id' => $mmClinic->id,
                'service_id' => $service->id,
                'scheduled_at' => $scheduledDate,
                'duration_minutes' => 30,
                'type' => 'consultation',
                'status' => $status,
                'reason' => $this->getRandomReason(),
                'notes' => 'Test future appointment created by seeder',
                'estimated_cost' => rand(500, 2000),
            ]);

            $this->command->info("Created future appointment #{$i}: {$appointment->appointment_number} - {$status} on {$scheduledDate->format('M d, Y')}");
        }

        $this->command->info("\n✅ Test data seeding completed!");
        $this->command->info("Pet Owner: {$petOwner->email}");
        $this->command->info("Pets created: " . count($createdPets));
        $this->command->info("Clinic: {$mmClinic->clinic_name}");
        $this->command->info("Total appointments: 20 (10 past, 10 future)");
    }

    /**
     * Get a random appointment reason.
     */
    private function getRandomReason(): string
    {
        $reasons = [
            'Annual checkup and vaccination',
            'Follow-up consultation',
            'Skin irritation examination',
            'Dental cleaning',
            'General wellness check',
            'Weight management consultation',
            'Behavioral consultation',
            'Parasite prevention',
            'Vaccination booster',
            'Health certificate request',
        ];

        return $reasons[array_rand($reasons)];
    }
}
