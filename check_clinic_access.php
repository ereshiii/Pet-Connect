<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$appointmentId = 3;
$userId = 3;

$appointment = DB::table('appointments')->where('id', $appointmentId)->first();
$user = DB::table('users')->where('id', $userId)->first();
$clinic = DB::table('clinic_registrations')->where('user_id', $userId)->first();

echo "=== CLINIC ACCESS CHECK ===\n\n";
echo "Appointment ID: {$appointmentId}\n";
echo "Appointment clinic_id: {$appointment->clinic_id}\n";
echo "Appointment status: {$appointment->status}\n\n";

echo "User ID: {$userId}\n";
echo "User account_type: {$user->account_type}\n\n";

echo "Clinic Registration:\n";
if ($clinic) {
    echo "  Clinic ID: {$clinic->id}\n";
    echo "  Clinic Name: {$clinic->clinic_name}\n";
} else {
    echo "  No clinic registration found!\n";
}

echo "\n=== MATCH CHECK ===\n";
echo "Does appointment clinic_id ({$appointment->clinic_id}) === clinic registration id (" . ($clinic ? $clinic->id : 'null') . ")? ";
echo ($clinic && $appointment->clinic_id === $clinic->id) ? "YES ✓" : "NO ✗";
echo "\n";
