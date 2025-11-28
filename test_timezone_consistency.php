<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== DEBUGGING DATE TIMEZONE CONSISTENCY ===\n\n";

echo "App Timezone: " . config('app.timezone') . "\n";
echo "Database Timezone: " . config('database.connections.' . config('database.default') . '.timezone', 'UTC') . "\n\n";

// Get a recent appointment
$appointment = Appointment::orderBy('id', 'desc')->first();

if (!$appointment) {
    echo "No appointments found.\n";
    exit;
}

echo "Testing Appointment ID: {$appointment->id}\n";
echo "Stored in DB: {$appointment->scheduled_at->toDateTimeString()}\n";
echo "Stored timezone: {$appointment->scheduled_at->timezone}\n\n";

echo "=== TESTING DIFFERENT PARSING METHODS ===\n\n";

// Simulate form input
$testDate = '2025-12-05';
$testTime = '5:00 AM';

echo "Form Input:\n";
echo "  Date: {$testDate}\n";
echo "  Time: {$testTime}\n\n";

// Method 1: Parse with app timezone (current implementation)
$method1 = Carbon::parse($testDate . ' ' . $testTime, config('app.timezone'));
echo "Method 1 (Parse with app timezone):\n";
echo "  DateTime: {$method1->toDateTimeString()}\n";
echo "  ISO8601: {$method1->toIso8601String()}\n";
echo "  Timezone: {$method1->timezone}\n";
echo "  Format for display: {$method1->format('M j, Y g:i A')}\n\n";

// Method 2: Parse and then set timezone
$method2 = Carbon::parse($testDate . ' ' . $testTime)->setTimezone(config('app.timezone'));
echo "Method 2 (Parse then setTimezone):\n";
echo "  DateTime: {$method2->toDateTimeString()}\n";
echo "  ISO8601: {$method2->toIso8601String()}\n";
echo "  Timezone: {$method2->timezone}\n";
echo "  Format for display: {$method2->format('M j, Y g:i A')}\n\n";

// Method 3: Create from format
$method3 = Carbon::createFromFormat('Y-m-d g:i A', $testDate . ' ' . $testTime, config('app.timezone'));
echo "Method 3 (createFromFormat with timezone):\n";
echo "  DateTime: {$method3->toDateTimeString()}\n";
echo "  ISO8601: {$method3->toIso8601String()}\n";
echo "  Timezone: {$method3->timezone}\n";
echo "  Format for display: {$method3->format('M j, Y g:i A')}\n\n";

echo "=== DISPLAY METHODS ===\n\n";

// Get actual DB value
$dbValue = $appointment->scheduled_at;

echo "Database Value: {$dbValue->toDateTimeString()}\n";
echo "Database Timezone: {$dbValue->timezone}\n\n";

// Method A: Direct format (NO timezone conversion)
$displayA = $dbValue->format('F j, Y');
echo "Display A (Direct format):\n";
echo "  Date: {$displayA}\n";
echo "  Time: {$dbValue->format('g:i A')}\n\n";

// Method B: With timezone conversion
$displayB = $dbValue->timezone('Asia/Manila')->format('F j, Y');
echo "Display B (With timezone conversion):\n";
echo "  Date: {$displayB}\n";
echo "  Time: {$dbValue->timezone('Asia/Manila')->format('g:i A')}\n\n";

// Method C: Copy and convert
$dbCopy = $dbValue->copy()->timezone('Asia/Manila');
$displayC = $dbCopy->format('F j, Y');
echo "Display C (Copy then convert):\n";
echo "  Date: {$displayC}\n";
echo "  Time: {$dbCopy->format('g:i A')}\n\n";

echo "=== CHECKING IF DATES MATCH ===\n\n";

echo "Do all display methods show same date?\n";
echo "  A vs B: " . ($displayA === $displayB ? '✅ MATCH' : '❌ DIFFERENT') . "\n";
echo "  B vs C: " . ($displayB === $displayC ? '✅ MATCH' : '❌ DIFFERENT') . "\n";
echo "  A vs C: " . ($displayA === $displayC ? '✅ MATCH' : '❌ DIFFERENT') . "\n\n";

echo "=== CHECKING CALENDAR VS SHOW OUTPUT ===\n\n";

// Simulate calendar transformation
$calendarDate = $appointment->scheduled_at->timezone('Asia/Manila')->format('M j, Y');
$calendarTime = $appointment->scheduled_at->timezone('Asia/Manila')->format('g:i A');

// Simulate show transformation (after fix)
$showDate = $appointment->scheduled_at->timezone('Asia/Manila')->format('F j, Y');
$showTime = $appointment->scheduled_at->timezone('Asia/Manila')->format('g:i A');

echo "Calendar output:\n";
echo "  Date: {$calendarDate}\n";
echo "  Time: {$calendarTime}\n\n";

echo "Show (details) output:\n";
echo "  Date: {$showDate}\n";
echo "  Time: {$showTime}\n\n";

echo "Time match: " . ($calendarTime === $showTime ? '✅ YES' : '❌ NO') . "\n";
echo "Date format difference: Calendar uses 'M j, Y' (short month), Show uses 'F j, Y' (full month)\n";
