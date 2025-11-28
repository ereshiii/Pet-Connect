<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== APPOINTMENT DATE TRANSFORMATION TEST ===\n\n";

// Get a few confirmed appointments
$appointments = Appointment::where('status', 'confirmed')
    ->with(['pet', 'service'])
    ->orderBy('scheduled_at')
    ->limit(3)
    ->get();

foreach ($appointments as $appointment) {
    echo "Appointment ID: {$appointment->id}\n";
    echo "  Pet: {$appointment->pet->name}\n";
    echo "  scheduled_at (raw DB): {$appointment->getRawOriginal('scheduled_at')}\n";
    echo "  scheduled_at (Carbon): {$appointment->scheduled_at}\n";
    echo "  scheduled_at timezone: {$appointment->scheduled_at->timezone}\n";
    
    // Simulate controller transformation
    $transformedDate = $appointment->scheduled_at->timezone('Asia/Manila')->format('Y-m-d');
    $transformedTime = $appointment->scheduled_at->timezone('Asia/Manila')->format('H:i');
    
    echo "  Transformed date: {$transformedDate}\n";
    echo "  Transformed time: {$transformedTime}\n";
    
    // Check what the date would be without timezone conversion
    $withoutTz = Carbon::parse($appointment->scheduled_at)->format('Y-m-d');
    echo "  Without timezone: {$withoutTz}\n";
    
    echo "\n";
}

echo "\n=== TESTING SPECIFIC SCENARIO ===\n";
echo "If scheduled_at is '2025-11-27 23:30:00' UTC:\n";
$testDate = Carbon::parse('2025-11-27 23:30:00', 'UTC');
echo "  UTC format: {$testDate->format('Y-m-d H:i:s T')}\n";
echo "  Asia/Manila: {$testDate->timezone('Asia/Manila')->format('Y-m-d H:i:s T')}\n";
echo "  Date in Manila: {$testDate->timezone('Asia/Manila')->format('Y-m-d')}\n\n";

echo "If scheduled_at is '2025-11-27 17:30:00' (no timezone):\n";
$testDate2 = Carbon::parse('2025-11-27 17:30:00');
echo "  Default timezone: {$testDate2->format('Y-m-d H:i:s T')}\n";
echo "  To Asia/Manila: {$testDate2->timezone('Asia/Manila')->format('Y-m-d H:i:s T')}\n";
echo "  Date in Manila: {$testDate2->timezone('Asia/Manila')->format('Y-m-d')}\n";
