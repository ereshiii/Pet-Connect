<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== APPOINTMENT DATA DEBUG ===\n\n";

// Check appointments
$appointments = \App\Models\Appointment::with(['pet.owner', 'clinic', 'service'])->get();
echo "Total appointments: " . $appointments->count() . "\n\n";

foreach ($appointments as $appointment) {
    echo "Appointment ID: " . $appointment->id . "\n";
    echo "Clinic ID: " . $appointment->clinic_id . "\n";
    echo "Owner ID: " . $appointment->owner_id . "\n";
    echo "Status: " . $appointment->status . "\n";
    echo "Scheduled At: " . $appointment->scheduled_at . "\n";
    echo "Pet: " . ($appointment->pet ? $appointment->pet->name : 'No pet') . "\n";
    echo "Clinic: " . ($appointment->clinic ? $appointment->clinic->clinic_name : 'No clinic') . "\n";
    echo "Service: " . ($appointment->service ? $appointment->service->name : 'No service') . "\n";
    echo "Owner: " . ($appointment->pet && $appointment->pet->owner ? $appointment->pet->owner->name : 'No owner') . "\n";
    echo "---\n";
}

// Check clinic registrations
echo "\nClinic registrations:\n";
$clinics = \App\Models\ClinicRegistration::with('user')->get();
foreach ($clinics as $clinic) {
    echo "Clinic Registration ID: " . $clinic->id . "\n";
    echo "Clinic Name: " . $clinic->clinic_name . "\n";
    echo "User ID: " . $clinic->user_id . "\n";
    echo "User: " . ($clinic->user ? $clinic->user->name : 'No user') . "\n";
    echo "User Account Type: " . ($clinic->user ? $clinic->user->account_type : 'N/A') . "\n";
    echo "User isClinic(): " . ($clinic->user ? ($clinic->user->isClinic() ? 'true' : 'false') : 'N/A') . "\n";
    echo "Status: " . $clinic->status . "\n";
    echo "---\n";
}

// Check if the clinic user can see appointments
echo "\nTesting clinic appointment query:\n";
$clinicRegistration = \App\Models\ClinicRegistration::first();
if ($clinicRegistration) {
    $clinicAppointments = \App\Models\Appointment::where('clinic_id', $clinicRegistration->id)->get();
    echo "Clinic ID " . $clinicRegistration->id . " has " . $clinicAppointments->count() . " appointments\n";
    
    // Test the exact query from ClinicAppointmentsController
    $user = $clinicRegistration->user;
    if ($user) {
        echo "User " . $user->name . " (ID: " . $user->id . ") authentication:\n";
        echo "- isClinic(): " . ($user->isClinic() ? 'true' : 'false') . "\n";
        echo "- account_type: " . $user->account_type . "\n";
        echo "- clinicRegistration exists: " . ($user->clinicRegistration ? 'true' : 'false') . "\n";
        
        if ($user->clinicRegistration) {
            echo "- clinicRegistration ID: " . $user->clinicRegistration->id . "\n";
            
            // Test the exact controller query with 'upcoming' filter
            $upcomingQuery = \App\Models\Appointment::with(['pet.owner', 'pet.petType', 'service'])
                ->where('clinic_id', $user->clinicRegistration->id)
                ->whereDate('scheduled_at', '>=', \Carbon\Carbon::today());
            echo "- 'Upcoming' filter returns: " . $upcomingQuery->count() . " appointments\n";
            
            // Test with today's filter
            $todayQuery = \App\Models\Appointment::with(['pet.owner', 'pet.petType', 'service'])
                ->where('clinic_id', $user->clinicRegistration->id)
                ->whereDate('scheduled_at', \Carbon\Carbon::today());
            echo "- Today's appointments: " . $todayQuery->count() . "\n";
            echo "- Today's date: " . \Carbon\Carbon::today() . "\n";
            echo "- Appointment date: " . $appointments->first()->scheduled_at->toDateString() . "\n";
        }
    }
}