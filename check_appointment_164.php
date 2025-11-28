<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;

echo "=== APPOINTMENT 164 DETAILED CHECK ===\n\n";

$appointment = Appointment::with(['pet.breed', 'pet.type', 'service', 'clinicRegistration'])
    ->find(164);

if (!$appointment) {
    echo "Appointment 164 not found!\n";
    exit;
}

echo "--- RAW APPOINTMENT DATA ---\n";
echo "ID: {$appointment->id}\n";
echo "Pet ID: {$appointment->pet_id}\n";
echo "Service ID: {$appointment->service_id}\n";
echo "scheduled_at (raw): {$appointment->getRawOriginal('scheduled_at')}\n";
echo "scheduled_at (Carbon): {$appointment->scheduled_at}\n\n";

echo "--- PET DATA ---\n";
$pet = $appointment->pet;
echo "Pet name: {$pet->name}\n";
echo "Pet breed_id: " . ($pet->breed_id ?? 'NULL') . "\n";
echo "Pet breed (string attr): " . ($pet->getAttributeValue('breed') ?? 'NULL') . "\n";
echo "Pet type_id: " . ($pet->type_id ?? 'NULL') . "\n";
echo "Breed relationship loaded: " . ($pet->relationLoaded('breed') ? 'YES' : 'NO') . "\n";
echo "Type relationship loaded: " . ($pet->relationLoaded('type') ? 'YES' : 'NO') . "\n";

if ($pet->breed_id && $pet->relationLoaded('breed')) {
    $breed = $pet->getRelation('breed');
    echo "Breed from relationship: " . ($breed ? $breed->name : 'NULL') . "\n";
}

if ($pet->type_id && $pet->relationLoaded('type')) {
    $type = $pet->getRelation('type');
    echo "Type from relationship: " . ($type ? $type->name : 'NULL') . "\n";
}

echo "Pet birth_date: " . ($pet->birth_date ?? 'NULL') . "\n";
echo "Pet calculated_age: " . ($pet->calculated_age ?? 'NULL') . "\n\n";

echo "--- SERVICE DATA ---\n";
if ($appointment->service) {
    echo "Service name: {$appointment->service->name}\n";
    echo "Service duration_minutes: {$appointment->service->duration_minutes}\n";
} else {
    echo "No service attached\n";
}

echo "\n--- CONTROLLER TRANSFORMATION SIMULATION ---\n";
$breedRelation = $pet->breed_id && $pet->relationLoaded('breed') ? $pet->getRelation('breed') : null;
$typeRelation = $pet->type_id && $pet->relationLoaded('type') ? $pet->getRelation('type') : null;

$petData = [
    'id' => $pet->id,
    'name' => $pet->name,
    'type' => $typeRelation ? $typeRelation->name : 'Unknown',
    'breed' => $breedRelation ? $breedRelation->name : ($pet->getAttributeValue('breed') ?? 'Unknown'),
    'age' => $pet->birth_date ? $pet->calculated_age : 'Unknown',
    'weight' => $pet->weight
];

echo "Transformed pet data:\n";
print_r($petData);

$duration = ($appointment->service && $appointment->service->duration_minutes ? $appointment->service->duration_minutes : $appointment->duration_minutes) . ' minutes';
$type = $appointment->service ? $appointment->service->name : ucfirst($appointment->type);

echo "\nTransformed duration: {$duration}\n";
echo "Transformed type: {$type}\n";
