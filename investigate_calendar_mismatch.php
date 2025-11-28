<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== CALENDAR DATE MISMATCH INVESTIGATION ===\n\n";

// Get appointment 164
$appointment = Appointment::find(164);

if (!$appointment) {
    echo "Appointment 164 not found!\n";
    exit;
}

echo "--- APPOINTMENT 164 DATES ---\n";
echo "scheduled_at (raw DB): {$appointment->getRawOriginal('scheduled_at')}\n";
echo "scheduled_at (Carbon): {$appointment->scheduled_at}\n";
echo "scheduled_at timezone: {$appointment->scheduled_at->timezone}\n\n";

echo "--- CONTROLLER TRANSFORMATIONS ---\n";

// AppointmentController@show transformation (lines 713-714)
$showDate = $appointment->scheduled_at->format('F j, Y');
$showTime = $appointment->scheduled_at->format('g:i A');
echo "show() method:\n";
echo "  date: {$showDate}\n";
echo "  time: {$showTime}\n\n";

// AppointmentController@index/calendar transformation (lines 84-85)
$calendarDate = $appointment->scheduled_at->timezone('Asia/Manila')->format('Y-m-d');
$calendarTime = $appointment->scheduled_at->timezone('Asia/Manila')->format('H:i:s');
$formattedDate = $appointment->scheduled_at->timezone('Asia/Manila')->format('M j, Y');
$formattedTime = $appointment->scheduled_at->timezone('Asia/Manila')->format('g:i A');

echo "index()/calendar() methods:\n";
echo "  appointment_date: {$calendarDate}\n";
echo "  appointment_time: {$calendarTime}\n";
echo "  formatted_date: {$formattedDate}\n";
echo "  formatted_time: {$formattedTime}\n\n";

echo "--- COMPARISON ---\n";
echo "Show date matches calendar date? " . ($showDate === $formattedDate ? 'YES' : 'NO') . "\n";
echo "Show time matches calendar time? " . ($showTime === $formattedTime ? 'YES' : 'NO') . "\n";

if ($showDate !== $formattedDate || $showTime !== $formattedTime) {
    echo "\n❌ MISMATCH FOUND!\n";
    echo "Details page shows: {$showDate} at {$showTime}\n";
    echo "Calendar shows: {$formattedDate} at {$formattedTime}\n";
} else {
    echo "\n✅ Dates match!\n";
}
