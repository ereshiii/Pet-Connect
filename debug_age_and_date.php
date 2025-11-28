<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use App\Models\Pet;

echo "=== DEBUGGING AGE AND DATE DISPLAY ===\n\n";

// Check a pet's age attribute
$pet = Pet::with('breed', 'type')->whereNotNull('birth_date')->first();

if ($pet) {
    echo "Pet ID: {$pet->id}\n";
    echo "Name: {$pet->name}\n";
    echo "Birth Date: {$pet->birth_date}\n";
    echo "calculated_age attribute: {$pet->calculated_age}\n";
    echo "age_in_years attribute: " . ($pet->age_in_years ?? 'N/A') . "\n\n";
}

// Check appointment transformation
$apt = Appointment::with(['pet.breed', 'pet.type', 'service'])->find(164);

if (!$apt) {
    $apt = Appointment::with(['pet.breed', 'pet.type', 'service'])->orderBy('id', 'desc')->first();
}

echo "=== APPOINTMENT TRANSFORMATION TEST ===\n\n";
echo "Appointment ID: {$apt->id}\n\n";

// What show() method sends for pet age
$petAge = $apt->pet->birth_date ? $apt->pet->calculated_age : 'Unknown';
echo "Pet age (show method): {$petAge}\n";
echo "Type: " . gettype($petAge) . "\n\n";

// What calendar() method sends
$calendarPet = [
    'age' => $apt->pet->birth_date ? $apt->pet->calculated_age : 'Unknown',
];
echo "Calendar pet age: {$calendarPet['age']}\n\n";

echo "=== DATE TRANSFORMATION ===\n\n";

// Current backend sends
$backendData = [
    'appointment_date' => $apt->scheduled_at->timezone('Asia/Manila')->format('Y-m-d'),
    'formatted_date' => $apt->scheduled_at->timezone('Asia/Manila')->format('M j, Y'),
    'formatted_time' => $apt->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
];

echo "Backend sends:\n";
echo "  appointment_date: {$backendData['appointment_date']}\n";
echo "  formatted_date: {$backendData['formatted_date']}\n";
echo "  formatted_time: {$backendData['formatted_time']}\n\n";

echo "=== SIMPLIFIED APPROACH ===\n\n";
echo "Instead of:\n";
echo "  - calendar event using appointment_date for filtering\n";
echo "  - calendar event using formatted_date for display\n\n";

echo "Use:\n";
echo "  - Single 'date' field with formatted string for both display and filtering\n";
echo "  - Compare formatted dates directly (Dec 5, 2025 === Dec 5, 2025)\n";
echo "  - NO date parsing at all on frontend\n";
