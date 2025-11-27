<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ClinicRegistration;
use App\Models\ClinicOperatingHour;

echo "Manual Sync Script for MM's Clinic\n";
echo str_repeat('=', 70) . "\n\n";

$clinic = ClinicRegistration::find(32);

if (!$clinic) {
    echo "Clinic not found!\n";
    exit(1);
}

echo "Syncing operating hours for: {$clinic->clinic_name}\n\n";

$clinicId = $clinic->clinic_id ?? $clinic->id;
$hours = ClinicOperatingHour::where('clinic_id', $clinicId)->get();

$operatingHoursJson = [];
$daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

foreach ($daysOfWeek as $day) {
    $dayHours = $hours->where('day_of_week', $day)->first();
    
    if (!$dayHours) {
        // No record for this day - treat as closed
        $operatingHoursJson[$day] = ['closed' => true];
    } elseif ($dayHours->is_closed) {
        // Explicitly marked as closed
        $operatingHoursJson[$day] = ['closed' => true];
    } else {
        // Open with times
        $operatingHoursJson[$day] = [
            'open' => $dayHours->opening_time,
            'close' => $dayHours->closing_time,
        ];
    }
}

echo "New operating_hours JSON:\n";
echo json_encode($operatingHoursJson, JSON_PRETTY_PRINT) . "\n\n";

// Update the clinic
$clinic->update([
    'operating_hours' => $operatingHoursJson,
]);

echo "✅ Successfully synced operating hours!\n\n";

// Verify
$clinic->refresh();
echo "Verification:\n";
echo "-------------\n";
foreach ($clinic->operating_hours as $day => $hours) {
    if (isset($hours['closed'])) {
        echo "  $day: CLOSED\n";
    } else {
        echo "  $day: {$hours['open']} - {$hours['close']}\n";
    }
}
