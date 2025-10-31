<?php

// Simple test script to check what's in the database
require_once __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the test user
$user = \App\Models\User::where('email', 'testclinic@example.com')->first();

if (!$user) {
    echo "Test user not found!\n";
    exit;
}

echo "Test User: {$user->email}\n";
echo "Account Type: {$user->account_type}\n\n";

// Check clinic registration
$registration = $user->clinicRegistration;

if ($registration) {
    echo "Clinic Registration Found:\n";
    echo "ID: {$registration->id}\n";
    echo "Status: {$registration->status}\n";
    echo "Clinic Name: " . ($registration->clinic_name ?: 'EMPTY') . "\n";
    echo "Email: " . ($registration->email ?: 'EMPTY') . "\n";
    echo "Phone: " . ($registration->phone ?: 'EMPTY') . "\n";
    echo "Region: " . ($registration->region ?: 'EMPTY') . "\n";
    echo "Province: " . ($registration->province ?: 'EMPTY') . "\n";
    echo "City: " . ($registration->city ?: 'EMPTY') . "\n";
    echo "Barangay: " . ($registration->barangay ?: 'EMPTY') . "\n";
    echo "Street Address: " . ($registration->street_address ?: 'EMPTY') . "\n";
    echo "Operating Hours: " . (empty($registration->operating_hours) ? 'EMPTY' : 'SET') . "\n";
    echo "Services: " . (empty($registration->services) ? 'EMPTY' : 'SET (' . count($registration->services) . ' items)') . "\n";
    echo "Veterinarians: " . (empty($registration->veterinarians) ? 'EMPTY' : 'SET (' . count($registration->veterinarians) . ' items)') . "\n";
    echo "Certification Proofs: " . (empty($registration->certification_proofs) ? 'EMPTY' : 'SET (' . count($registration->certification_proofs) . ' items)') . "\n";
    
    echo "\nIs Complete: " . ($registration->isComplete() ? 'YES' : 'NO') . "\n";
    
    if ($registration->submitted_at) {
        echo "Submitted At: {$registration->submitted_at}\n";
    }
    
    if ($registration->approved_at) {
        echo "Approved At: {$registration->approved_at}\n";
    }
} else {
    echo "No clinic registration found.\n";
}