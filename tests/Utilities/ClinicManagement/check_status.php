<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$registration = App\Models\ClinicRegistration::where('user_id', 1)->first();

if ($registration) {
    echo "Current Status: " . $registration->status . "\n";
    echo "Clinic Name: " . $registration->clinic_name . "\n";
    echo "Created: " . $registration->created_at . "\n";
    echo "Updated: " . $registration->updated_at . "\n";
} else {
    echo "No registration found for user ID 1\n";
}