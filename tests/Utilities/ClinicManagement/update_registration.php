<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$registration = App\Models\ClinicRegistration::where('user_id', 1)->first();

if ($registration) {
    // Update the registration with complete data
    $registration->update([
        'clinic_name' => 'Test Veterinary Clinic',
        'clinic_address' => '123 Test Street, Test City',
        'clinic_phone' => '+1234567890',
        'clinic_email' => 'test@clinic.com',
        'veterinarian_name' => 'Dr. Test Vet',
        'license_number' => 'VET123456',
        'years_experience' => 5,
        'specializations' => json_encode(['General Practice', 'Surgery']),
        'emergency_services' => true,
        'operating_hours' => json_encode([
            'monday' => '9:00-17:00',
            'tuesday' => '9:00-17:00',
            'wednesday' => '9:00-17:00',
            'thursday' => '9:00-17:00',
            'friday' => '9:00-17:00',
            'saturday' => '9:00-13:00',
            'sunday' => 'Closed'
        ]),
        'status' => 'pending',
        'submitted_at' => now()
    ]);
    
    echo "Updated registration to pending status with complete data\n";
    echo "Current Status: " . $registration->fresh()->status . "\n";
} else {
    echo "No registration found for user ID 1\n";
}