<?php

use App\Models\Appointment;
use App\Models\ClinicRegistration;
use App\Models\ClinicService;
use App\Models\Pet;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // Create pet owner
    $this->petOwner = User::create([
        'email' => 'petowner@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]);

    // Create clinic user with admin privileges to simulate clinic access
    $this->clinicUser = User::create([
        'email' => 'clinic@test.com',
        'password' => bcrypt('password'),
        'is_admin' => true,
        'email_verified_at' => now()
    ]);

    // Create clinic registration
    $this->clinic = ClinicRegistration::create([
        'user_id' => $this->clinicUser->id,
        'clinic_name' => 'Test Veterinary Clinic',
        'clinic_address' => '123 Test Street',
        'clinic_phone' => '+1234567890',
        'license_number' => 'LIC123456',
        'status' => 'approved',
        'verification_status' => 'verified'
    ]);

    // Create pet for the owner
    $this->pet = Pet::create([
        'user_id' => $this->petOwner->id,
        'name' => 'Buddy',
        'species' => 'Dog',
        'breed' => 'Golden Retriever',
        'age' => 3,
        'weight' => 25.5,
        'color' => 'Golden',
        'gender' => 'Male'
    ]);

    // Create clinic service with correct field name
    $this->service = ClinicService::create([
        'clinic_id' => $this->clinic->id,
        'name' => 'General Checkup',
        'description' => 'Comprehensive health examination',
        'base_price' => 50.00, // Using correct field name
        'duration' => 30,
        'is_active' => true
    ]);
});

test('user can book appointment with correct status', function () {
    $this->actingAs($this->petOwner);

    $appointmentData = [
        'clinic_id' => $this->clinic->id,
        'pet_id' => $this->pet->id,
        'service_id' => $this->service->id,
        'appointment_date' => Carbon::tomorrow()->toDateString(),
        'appointment_time' => '10:00:00',
        'notes' => 'Regular checkup for Buddy'
    ];

    $response = $this->post(route('appointmentsStore'), $appointmentData);

    $response->assertRedirect();
    
    // Verify appointment was created with correct status
    $appointment = Appointment::latest()->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->status)->toBe('scheduled'); // Should be 'scheduled', not 'pending'
    expect($appointment->clinic_id)->toBe($this->clinic->id);
    expect($appointment->pet_id)->toBe($this->pet->id);
    expect($appointment->service_id)->toBe($this->service->id);
});

test('clinic can view appointment with correct service cost', function () {
    // Create appointment
    $appointment = Appointment::create([
        'clinic_id' => $this->clinic->id,
        'pet_id' => $this->pet->id,
        'service_id' => $this->service->id,
        'appointment_date' => Carbon::tomorrow()->toDateString(),
        'appointment_time' => '10:00:00',
        'status' => 'scheduled',
        'notes' => 'Test appointment'
    ]);

    $this->actingAs($this->clinicUser);

    $response = $this->get(route('clinicAppointmentDetails', $appointment->id));

    $response->assertOk();
    
    // Verify the appointment data includes the service with correct pricing
    $responseData = $response->getOriginalContent()->getData();
    expect($responseData)->toHaveKey('appointment');
    
    $appointmentData = $responseData['appointment'];
    expect($appointmentData->status)->toBe('scheduled');
    expect($appointmentData->service->base_price)->toBe($this->service->base_price);
});

test('clinic can update appointment status', function () {
    $appointment = Appointment::create([
        'clinic_id' => $this->clinic->id,
        'pet_id' => $this->pet->id,
        'service_id' => $this->service->id,
        'appointment_date' => Carbon::tomorrow()->toDateString(),
        'appointment_time' => '10:00:00',
        'status' => 'scheduled',
        'notes' => 'Test appointment'
    ]);

    $this->actingAs($this->clinicUser);

    $response = $this->patch(route('clinicAppointments.updateStatus', $appointment->id), [
        'status' => 'confirmed'
    ]);

    $response->assertRedirect();
    
    // Verify status was updated
    $appointment->refresh();
    expect($appointment->status)->toBe('confirmed');
});

test('appointment conflict prevention works', function () {
    // Create existing appointment
    Appointment::create([
        'clinic_id' => $this->clinic->id,
        'pet_id' => $this->pet->id,
        'service_id' => $this->service->id,
        'appointment_date' => Carbon::tomorrow()->toDateString(),
        'appointment_time' => '10:00:00',
        'status' => 'scheduled'
    ]);

    $this->actingAs($this->petOwner);

    // Try to book conflicting appointment
    $conflictingData = [
        'clinic_id' => $this->clinic->id,
        'pet_id' => $this->pet->id,
        'service_id' => $this->service->id,
        'appointment_date' => Carbon::tomorrow()->toDateString(),
        'appointment_time' => '10:00:00', // Same time slot
        'notes' => 'Conflicting appointment'
    ];

    $response = $this->post(route('appointmentsStore'), $conflictingData);

    // Should redirect back with error
    $response->assertSessionHasErrors(['appointment_time']);
});

test('appointment workflow maintains data integrity', function () {
    $this->actingAs($this->petOwner);

    // User books appointment
    $appointmentData = [
        'clinic_id' => $this->clinic->id,
        'pet_id' => $this->pet->id,
        'service_id' => $this->service->id,
        'appointment_date' => Carbon::tomorrow()->toDateString(),
        'appointment_time' => '14:00:00',
        'notes' => 'Integration test appointment'
    ];

    $bookingResponse = $this->post(route('appointmentsStore'), $appointmentData);
    $bookingResponse->assertRedirect();

    $appointment = Appointment::latest()->first();
    
    // Switch to clinic user
    $this->actingAs($this->clinicUser);

    // Clinic views appointment
    $viewResponse = $this->get(route('clinicAppointmentDetails', $appointment->id));
    $viewResponse->assertOk();

    // Clinic updates status
    $updateResponse = $this->patch(route('clinicAppointments.updateStatus', $appointment->id), [
        'status' => 'confirmed'
    ]);
    $updateResponse->assertRedirect();

    // Switch back to user
    $this->actingAs($this->petOwner);

    // User views updated appointment
    $userViewResponse = $this->get(route('appointmentDetails', $appointment->id));
    $userViewResponse->assertOk();

    // Verify data consistency throughout workflow
    $appointment->refresh();
    expect($appointment->status)->toBe('confirmed');
    expect($appointment->clinic_id)->toBe($this->clinic->id);
    expect($appointment->service_id)->toBe($this->service->id);
    expect($appointment->service->base_price)->toBe($this->service->base_price);
});