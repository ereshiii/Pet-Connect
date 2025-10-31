<?php

namespace Tests\Feature\AppointmentSystem;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_appointment(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['owner_id' => $user->id]);
        $clinic = Clinic::factory()->create();

        $appointment = Appointment::create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'clinic_id' => $clinic->id,
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'service_type' => 'checkup',
            'status' => 'scheduled',
            'notes' => 'Regular checkup for yearly vaccination',
            'estimated_duration' => 60,
        ]);

        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'clinic_id' => $clinic->id,
            'service_type' => 'checkup',
            'status' => 'scheduled',
            'estimated_duration' => 60,
        ]);

        $this->assertEquals('checkup', $appointment->service_type);
        $this->assertEquals('scheduled', $appointment->status);
    }

    public function test_appointment_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $appointment = Appointment::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $appointment->user);
        $this->assertEquals($user->id, $appointment->user->id);
    }

    public function test_appointment_belongs_to_pet(): void
    {
        $pet = Pet::factory()->create();
        $appointment = Appointment::factory()->create(['pet_id' => $pet->id]);

        $this->assertInstanceOf(Pet::class, $appointment->pet);
        $this->assertEquals($pet->id, $appointment->pet->id);
    }

    public function test_appointment_belongs_to_clinic(): void
    {
        $clinic = Clinic::factory()->create();
        $appointment = Appointment::factory()->create(['clinic_id' => $clinic->id]);

        $this->assertInstanceOf(Clinic::class, $appointment->clinic);
        $this->assertEquals($clinic->id, $appointment->clinic->id);
    }

    public function test_appointment_full_datetime(): void
    {
        $appointment = Appointment::factory()->create([
            'appointment_date' => '2024-03-15',
            'appointment_time' => '14:30:00',
        ]);

        $expectedDateTime = '2024-03-15 14:30:00';
        $this->assertEquals($expectedDateTime, $appointment->full_datetime->format('Y-m-d H:i:s'));
    }

    public function test_appointment_formatted_date(): void
    {
        $appointment = Appointment::factory()->create([
            'appointment_date' => '2024-03-15',
        ]);

        $this->assertEquals('March 15, 2024', $appointment->formatted_date);
    }

    public function test_appointment_formatted_time(): void
    {
        $appointment = Appointment::factory()->create([
            'appointment_time' => '14:30:00',
        ]);

        $this->assertEquals('2:30 PM', $appointment->formatted_time);
    }

    public function test_appointment_status_display(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'scheduled']);
        $this->assertEquals('Scheduled', $appointment->status_display);

        $appointment->status = 'confirmed';
        $this->assertEquals('Confirmed', $appointment->status_display);

        $appointment->status = 'completed';
        $this->assertEquals('Completed', $appointment->status_display);
    }

    public function test_appointment_service_type_display(): void
    {
        $appointment = Appointment::factory()->create(['service_type' => 'checkup']);
        $this->assertEquals('Checkup', $appointment->service_type_display);

        $appointment->service_type = 'vaccination';
        $this->assertEquals('Vaccination', $appointment->service_type_display);

        $appointment->service_type = 'grooming';
        $this->assertEquals('Grooming', $appointment->service_type_display);
    }

    public function test_appointment_can_be_cancelled(): void
    {
        // Future appointment
        $futureAppointment = Appointment::factory()->create([
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'scheduled',
        ]);
        $this->assertTrue($futureAppointment->can_be_cancelled);

        // Past appointment
        $pastAppointment = Appointment::factory()->create([
            'appointment_date' => now()->subDays(1)->format('Y-m-d'),
            'status' => 'scheduled',
        ]);
        $this->assertFalse($pastAppointment->can_be_cancelled);

        // Completed appointment
        $completedAppointment = Appointment::factory()->create([
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'completed',
        ]);
        $this->assertFalse($completedAppointment->can_be_cancelled);
    }

    public function test_appointment_can_be_rescheduled(): void
    {
        // Scheduled appointment
        $scheduledAppointment = Appointment::factory()->create([
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'scheduled',
        ]);
        $this->assertTrue($scheduledAppointment->can_be_rescheduled);

        // Confirmed appointment (within 24 hours)
        $confirmedAppointment = Appointment::factory()->create([
            'appointment_date' => now()->addHours(12)->format('Y-m-d'),
            'status' => 'confirmed',
        ]);
        $this->assertFalse($confirmedAppointment->can_be_rescheduled);
    }

    public function test_appointment_is_today(): void
    {
        $todayAppointment = Appointment::factory()->create([
            'appointment_date' => now()->format('Y-m-d'),
        ]);
        $this->assertTrue($todayAppointment->is_today);

        $tomorrowAppointment = Appointment::factory()->create([
            'appointment_date' => now()->addDay()->format('Y-m-d'),
        ]);
        $this->assertFalse($tomorrowAppointment->is_today);
    }

    public function test_appointment_is_upcoming(): void
    {
        $upcomingAppointment = Appointment::factory()->create([
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
        $this->assertTrue($upcomingAppointment->is_upcoming);

        $pastAppointment = Appointment::factory()->create([
            'appointment_date' => now()->subDay()->format('Y-m-d'),
        ]);
        $this->assertFalse($pastAppointment->is_upcoming);
    }

    public function test_can_filter_appointments_by_status(): void
    {
        $scheduledAppointment = Appointment::factory()->create(['status' => 'scheduled']);
        $confirmedAppointment = Appointment::factory()->create(['status' => 'confirmed']);
        $completedAppointment = Appointment::factory()->create(['status' => 'completed']);

        $scheduledAppointments = Appointment::byStatus('scheduled')->get();
        $this->assertCount(1, $scheduledAppointments);
        $this->assertEquals('scheduled', $scheduledAppointments->first()->status);
    }

    public function test_can_filter_upcoming_appointments(): void
    {
        $upcomingAppointment = Appointment::factory()->create([
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $pastAppointment = Appointment::factory()->create([
            'appointment_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $upcomingAppointments = Appointment::upcoming()->get();
        $this->assertCount(1, $upcomingAppointments);
        $this->assertEquals($upcomingAppointment->id, $upcomingAppointments->first()->id);
    }

    public function test_can_filter_today_appointments(): void
    {
        $todayAppointment = Appointment::factory()->create([
            'appointment_date' => now()->format('Y-m-d'),
        ]);

        $tomorrowAppointment = Appointment::factory()->create([
            'appointment_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $todayAppointments = Appointment::today()->get();
        $this->assertCount(1, $todayAppointments);
        $this->assertEquals($todayAppointment->id, $todayAppointments->first()->id);
    }

    public function test_appointment_estimated_end_time(): void
    {
        $appointment = Appointment::factory()->create([
            'appointment_time' => '14:30:00',
            'estimated_duration' => 60,
        ]);

        $endTime = $appointment->estimated_end_time;
        $this->assertEquals('15:30:00', $endTime->format('H:i:s'));
    }

    public function test_appointment_summary(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $pet = Pet::factory()->create(['name' => 'Buddy']);
        $clinic = Clinic::factory()->create(['name' => 'Pet Care Clinic']);

        $appointment = Appointment::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'clinic_id' => $clinic->id,
            'service_type' => 'checkup',
            'appointment_date' => '2024-03-15',
            'appointment_time' => '14:30:00',
        ]);

        $summary = $appointment->summary;

        $this->assertIsArray($summary);
        $this->assertEquals('checkup', $summary['service_type']);
        $this->assertEquals('2024-03-15', $summary['date']);
        $this->assertEquals('14:30:00', $summary['time']);
    }
}