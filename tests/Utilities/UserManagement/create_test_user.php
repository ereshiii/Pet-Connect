<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create a test clinic user
$user = \App\Models\User::firstOrCreate(
    ['email' => 'testclinic@example.com'],
    [
        'name' => 'Test Clinic User',
        'password' => bcrypt('password'),
        'account_type' => 'clinic',
        'email_verified_at' => now()
    ]
);

echo "Test clinic user created/found:\n";
echo "ID: {$user->id}\n";
echo "Email: {$user->email}\n";
echo "Account Type: {$user->account_type}\n";

// Check if clinic registration exists
$registration = $user->clinicRegistration;
if ($registration) {
    echo "\nClinic Registration Status: {$registration->status}\n";
} else {
    echo "\nNo clinic registration found.\n";
}

echo "\nYou can now test the registration flow at: http://petconnect.test\n";
echo "Login with: testclinic@example.com / password\n";