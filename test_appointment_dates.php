<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== APPOINTMENT DATE/TIME INVESTIGATION ===\n\n";

echo "App Timezone: " . config('app.timezone') . "\n\n";

// Get recent appointments
echo "--- RECENT APPOINTMENTS (raw scheduled_at values) ---\n";
$appointments = Appointment::with(['pet'])
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

foreach ($appointments as $apt) {
    echo "ID {$apt->id}: {$apt->pet->name}\n";
    echo "  scheduled_at (raw): {$apt->getRawOriginal('scheduled_at')}\n";
    echo "  scheduled_at (Carbon): {$apt->scheduled_at}\n";
    echo "  scheduled_at->format('Y-m-d'): " . $apt->scheduled_at->format('Y-m-d') . "\n";
    echo "  scheduled_at->format('g:i A'): " . $apt->scheduled_at->format('g:i A') . "\n";
    echo "  scheduled_at->timezone: {$apt->scheduled_at->timezone}\n";
    
    // Transform like controller does
    $transformedDate = $apt->scheduled_at->timezone('Asia/Manila')->format('Y-m-d');
    $transformedTime = $apt->scheduled_at->timezone('Asia/Manila')->format('g:i A');
    echo "  Transformed date: {$transformedDate}\n";
    echo "  Transformed time: {$transformedTime}\n\n";
}

echo "\n=== TEST DATE PARSING (like form does) ===\n";
$testDate = '2025-11-27';
$testTime = '2:30 PM';

// Simulate form processing
$time24 = str_ireplace([' AM', ' PM', 'AM', 'PM'], '', $testTime);
$isPM = stripos($testTime, 'PM') !== false;
$timeParts = explode(':', trim($time24));
$hours = (int)$timeParts[0];
$minutes = isset($timeParts[1]) ? $timeParts[1] : '00';

if ($isPM && $hours !== 12) {
    $hours += 12;
} elseif (!$isPM && $hours === 12) {
    $hours = 0;
}

$time24 = sprintf('%02d:%s', $hours, $minutes);
$dateTimeString = $testDate . ' ' . $time24;
$scheduledAt = Carbon::parse($dateTimeString, config('app.timezone'));

echo "Input: {$testDate} at {$testTime}\n";
echo "Parsed as: {$scheduledAt->toDateTimeString()}\n";
echo "Date only: {$scheduledAt->format('Y-m-d')}\n";
echo "Time only: {$scheduledAt->format('g:i A')}\n";
echo "Timezone: {$scheduledAt->timezone}\n";
