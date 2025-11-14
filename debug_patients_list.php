<?php

// Debug PatientsList controller logic

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pet;
use App\Models\Appointment;
use Carbon\Carbon;

echo "=== DEBUG: PatientsList Controller Logic ===\n\n";

$clinicId = 1;

// Test the query from the controller
$query = Pet::with([
    'owner', 
    'owner.profile',
    'type', 
    'breed',
    'healthConditions' => function ($q) {
        $q->where('is_active', true);
    },
    'vaccinations' => function ($q) use ($clinicId) {
        $q->where('clinic_id', $clinicId);
    }
])
->where('is_active', true);

$patients = $query->orderBy('name')->get();

echo "Found {$patients->count()} pets in database\n\n";

foreach ($patients as $pet) {
    echo "Pet: {$pet->name} (ID: {$pet->id})\n";
    echo "  Species: " . ($pet->species ?: 'null') . "\n";
    echo "  Breed: " . ($pet->breed ?: 'null') . "\n";
    echo "  Age: " . ($pet->age ?: 'null') . "\n";
    echo "  Owner: " . ($pet->owner ? $pet->owner->name : 'No owner') . "\n";
    
    // Check appointments for this pet at clinic
    $appointmentCount = Appointment::where('pet_id', $pet->id)
        ->where('clinic_id', $clinicId)
        ->count();
    echo "  Appointments at clinic {$clinicId}: {$appointmentCount}\n";
    
    // Get latest appointment
    $latestAppointment = Appointment::where('pet_id', $pet->id)
        ->where('clinic_id', $clinicId)
        ->orderBy('scheduled_at', 'desc')
        ->first();
    
    if ($latestAppointment) {
        echo "  Latest appointment: " . Carbon::parse($latestAppointment->scheduled_at)->format('M j, Y') . "\n";
    } else {
        echo "  Latest appointment: No visits\n";
    }
    
    echo "\n";
}

// Test the transformation logic for first pet
if ($patients->count() > 0) {
    echo "=== Testing transformation for first pet ===\n";
    $pet = $patients->first();
    $owner = $pet->owner;
    
    // Get the latest appointment for this pet at this clinic
    $latestAppointment = Appointment::where('pet_id', $pet->id)
        ->where('clinic_id', $clinicId)
        ->orderBy('scheduled_at', 'desc')
        ->first();
    
    $transformed = [
        'id' => $pet->id,
        'name' => $pet->name,
        'species' => $pet->species ?: ($pet->type ? $pet->type->species : 'Unknown'),
        'breed' => $pet->breed ?: ($pet->breed ? $pet->breed->name : 'Mixed breed'),
        'age' => $pet->age,
        'owner_name' => $owner ? $owner->name : 'Unknown Owner',
        'owner_phone' => $owner && $owner->profile ? $owner->profile->phone : 'No phone',
        'last_visit' => $latestAppointment 
            ? Carbon::parse($latestAppointment->scheduled_at)->format('M j, Y')
            : 'No visits',
        'vaccination_status' => 'unknown',
        'medical_conditions' => [],
    ];
    
    echo "Transformed data:\n";
    print_r($transformed);
}