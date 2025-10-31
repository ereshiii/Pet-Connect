<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CLINIC ACCOUNTS IN DATABASE ===\n\n";

$clinics = App\Models\User::where('account_type', 'clinic')
    ->with('clinicRegistration')
    ->get();

if ($clinics->isEmpty()) {
    echo "No clinic accounts found.\n";
} else {
    foreach ($clinics as $clinic) {
        echo "User ID: {$clinic->id}\n";
        echo "Name: {$clinic->name}\n";
        echo "Email: {$clinic->email}\n";
        
        if ($clinic->clinicRegistration) {
            $reg = $clinic->clinicRegistration;
            echo "Registration ID: {$reg->id}\n";
            echo "Clinic Name: " . ($reg->clinic_name ?: 'Not set') . "\n";
            echo "Status: {$reg->status}\n";
            echo "Submitted: " . ($reg->submitted_at ? $reg->submitted_at->format('Y-m-d H:i:s') : 'Not submitted') . "\n";
        } else {
            echo "Registration: No registration found\n";
        }
        echo "---\n";
    }
}

echo "\n=== CLINIC REGISTRATIONS ===\n\n";

$registrations = App\Models\ClinicRegistration::with('user')->get();

if ($registrations->isEmpty()) {
    echo "No clinic registrations found.\n";
} else {
    foreach ($registrations as $reg) {
        echo "Registration ID: {$reg->id}\n";
        echo "User: {$reg->user->name} ({$reg->user->email})\n";
        echo "Clinic Name: " . ($reg->clinic_name ?: 'Not set') . "\n";
        echo "Status: {$reg->status}\n";
        echo "---\n";
    }
}