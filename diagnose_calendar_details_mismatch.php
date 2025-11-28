<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== COMPREHENSIVE CALENDAR VS DETAILS MISMATCH TEST ===\n\n";

// Test with appointment ID 164 (the one user mentioned)
$apt = Appointment::with(['pet.breed', 'pet.type', 'service'])->find(164);

if (!$apt) {
    echo "Appointment #164 not found. Using latest appointment.\n";
    $apt = Appointment::with(['pet.breed', 'pet.type', 'service'])->orderBy('id', 'desc')->first();
}

echo "Testing Appointment ID: {$apt->id}\n";
echo "Stored scheduled_at: {$apt->scheduled_at}\n";
echo "Database timezone: {$apt->scheduled_at->timezone}\n\n";

echo "=== CALENDAR OUTPUT (calendar() method) ===\n\n";

$calendarOutput = [
    'formatted_date' => $apt->scheduled_at->timezone('Asia/Manila')->format('M j, Y'),
    'formatted_time' => $apt->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
    'appointment_date' => $apt->scheduled_at->timezone('Asia/Manila')->format('Y-m-d'),
    'scheduled_at' => $apt->scheduled_at->timezone('Asia/Manila')->toIso8601String(),
];

echo "formatted_date: {$calendarOutput['formatted_date']}\n";
echo "formatted_time: {$calendarOutput['formatted_time']}\n";
echo "appointment_date: {$calendarOutput['appointment_date']}\n";
echo "scheduled_at (ISO): {$calendarOutput['scheduled_at']}\n\n";

echo "=== SHOW/DETAILS OUTPUT (show() method) ===\n\n";

$showOutput = [
    'date' => $apt->scheduled_at->timezone('Asia/Manila')->format('F j, Y'),
    'time' => $apt->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
    'scheduledAt' => $apt->scheduled_at->timezone('Asia/Manila')->toIso8601String(),
];

echo "date: {$showOutput['date']}\n";
echo "time: {$showOutput['time']}\n";
echo "scheduledAt (ISO): {$showOutput['scheduledAt']}\n\n";

echo "=== COMPARISON ===\n\n";

$calendarDate = Carbon::parse($calendarOutput['appointment_date']);
$showDate = Carbon::parse($showOutput['scheduledAt'])->timezone('Asia/Manila');

echo "Calendar date (parsed): {$calendarDate->format('Y-m-d')}\n";
echo "Show date (parsed): {$showDate->format('Y-m-d')}\n";
echo "Dates match: " . ($calendarDate->format('Y-m-d') === $showDate->format('Y-m-d') ? '✅ YES' : '❌ NO') . "\n\n";

echo "Calendar time: {$calendarOutput['formatted_time']}\n";
echo "Show time: {$showOutput['time']}\n";
echo "Times match: " . ($calendarOutput['formatted_time'] === $showOutput['time'] ? '✅ YES' : '❌ NO') . "\n\n";

echo "=== POTENTIAL JAVASCRIPT TIMEZONE ISSUE ===\n\n";

$isoString = $showOutput['scheduledAt'];
echo "ISO8601 sent to frontend: {$isoString}\n\n";

echo "How JavaScript might interpret this:\n";
echo "  In Manila (GMT+8): " . Carbon::parse($isoString)->timezone('Asia/Manila')->format('F j, Y g:i A') . "\n";
echo "  In UTC (GMT+0): " . Carbon::parse($isoString)->timezone('UTC')->format('F j, Y g:i A') . "\n";
echo "  In US Pacific (GMT-8): " . Carbon::parse($isoString)->timezone('America/Los_Angeles')->format('F j, Y g:i A') . "\n\n";

echo "⚠️  If the user's browser timezone is NOT Asia/Manila,\n";
echo "    parsing {$isoString} with 'new Date()'\n";
echo "    could shift the date/time to their local timezone!\n\n";

echo "=== SOLUTION ===\n\n";
echo "Frontend should use the pre-formatted strings (date, time, formatted_date)\n";
echo "instead of parsing scheduledAt ISO string with JavaScript Date object.\n\n";

echo "Calendar uses: appointment.formatted_date (✅ Correct)\n";
echo "Details uses: appointment.date (✅ Correct)\n\n";

echo "Both should display the SAME date, just different formats:\n";
echo "  Calendar: '{$calendarOutput['formatted_date']}' (short month)\n";
echo "  Details: '{$showOutput['date']}' (full month)\n";
echo "  Same day: " . ($calendarDate->day === $showDate->day && $calendarDate->month === $showDate->month ? '✅ YES' : '❌ NO') . "\n";
