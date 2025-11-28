<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== CALENDAR DATE MISMATCH DEBUG (Asia/Shanghai Browser) ===\n\n";

$apt = Appointment::with(['pet.breed', 'pet.type', 'service'])->find(164);

if (!$apt) {
    $apt = Appointment::with(['pet.breed', 'pet.type', 'service'])->orderBy('id', 'desc')->first();
}

echo "Appointment ID: {$apt->id}\n";
echo "Stored in DB: {$apt->scheduled_at}\n";
echo "DB Timezone: {$apt->scheduled_at->timezone}\n\n";

echo "=== WHAT BACKEND SENDS ===\n\n";

// Calendar endpoint
$calendarData = [
    'appointment_date' => $apt->scheduled_at->timezone('Asia/Manila')->format('Y-m-d'),
    'formatted_date' => $apt->scheduled_at->timezone('Asia/Manila')->format('M j, Y'),
    'formatted_time' => $apt->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
];

echo "Calendar sends:\n";
echo "  appointment_date: {$calendarData['appointment_date']}\n";
echo "  formatted_date: {$calendarData['formatted_date']}\n";
echo "  formatted_time: {$calendarData['formatted_time']}\n\n";

// Show endpoint
$showData = [
    'date' => $apt->scheduled_at->timezone('Asia/Manila')->format('F j, Y'),
    'time' => $apt->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
];

echo "AppointmentDetails sends:\n";
echo "  date: {$showData['date']}\n";
echo "  time: {$showData['time']}\n\n";

echo "=== PROBLEM: appointment_date vs formatted_date ===\n\n";

// The issue is that appointment_date is Y-m-d format
// When Vue calendar component uses this, it might be parsing it
echo "appointment_date is: '{$calendarData['appointment_date']}' (YYYY-MM-DD format)\n";
echo "This is the format used by calendar event.date property\n\n";

echo "When JavaScript parses 'YYYY-MM-DD' without time:\n";
echo "  new Date('2025-12-05') treats it as UTC midnight\n";
echo "  In Asia/Shanghai (GMT+8): This becomes Dec 5, 2025 8:00 AM\n";
echo "  CORRECT behavior for dates without time\n\n";

echo "=== SOLUTION ===\n\n";
echo "The calendar should display formatted_date directly, not appointment_date!\n";
echo "formatted_date is already formatted as 'M j, Y' so no parsing needed.\n\n";

echo "Check AppointmentCalendar.vue:\n";
echo "  - Calendar event.date should be formatted_date (not appointment_date)\n";
echo "  - List view should use event.date (which will be formatted_date)\n";
echo "  - NO new Date() parsing should happen on display\n\n";

$date = '2025-12-05';
echo "Test: What happens when calendar uses appointment_date:\n";
echo "  Backend sends: '{$date}'\n";
echo "  Calendar stores as event.date: '{$date}'\n";
echo "  List view displays: event.date directly = '{$date}' (shows raw Y-m-d)\n";
echo "  WRONG! Should show 'Dec 5, 2025' instead\n\n";

echo "CORRECT approach:\n";
echo "  Backend sends formatted_date: '{$calendarData['formatted_date']}'\n";
echo "  Calendar stores as event.date: '{$calendarData['formatted_date']}'\n";
echo "  List view displays: '{$calendarData['formatted_date']}' directly\n";
echo "  CORRECT! Shows 'Dec 5, 2025'\n";
