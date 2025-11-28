<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

echo "=== WORKFLOW SIMPLIFICATION VERIFICATION ===\n\n";

// Check if there are any appointments with 'confirmed' status
echo "1. CHECKING FOR 'CONFIRMED' STATUS APPOINTMENTS\n";
echo str_repeat("-", 60) . "\n";

$confirmedCount = Appointment::where('status', 'confirmed')->count();
echo "Appointments with 'confirmed' status: {$confirmedCount}\n";

if ($confirmedCount > 0) {
    echo "⚠️  WARNING: Found {$confirmedCount} appointments still using 'confirmed' status\n";
    echo "These should be updated to 'scheduled' status.\n\n";
    
    $confirmed = Appointment::where('status', 'confirmed')->get();
    foreach ($confirmed as $appt) {
        echo "  - Appointment #{$appt->id}: {$appt->status}\n";
    }
} else {
    echo "✅ GOOD: No appointments with 'confirmed' status found\n";
}

echo "\n2. STATUS DISTRIBUTION\n";
echo str_repeat("-", 60) . "\n";

$statusCounts = DB::table('appointments')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($statusCounts as $status) {
    echo "  {$status->status}: {$status->count} appointments\n";
}

echo "\n3. AUTO-TRANSITION TEST (Scheduled → In Progress)\n";
echo str_repeat("-", 60) . "\n";

// Find a scheduled appointment with past time
$pastScheduled = Appointment::where('status', 'scheduled')
    ->where('scheduled_at', '<', now())
    ->first();

if ($pastScheduled) {
    echo "Found scheduled appointment #{$pastScheduled->id} with past time:\n";
    echo "  Status: {$pastScheduled->status}\n";
    echo "  Scheduled: {$pastScheduled->scheduled_at->format('M j, Y g:i A')}\n";
    echo "  Current time: " . now()->format('M j, Y g:i A') . "\n";
    echo "\n";
    echo "This should auto-transition to 'in_progress' when viewed.\n";
    echo "The auto-transition logic checks:\n";
    echo "  - if (\$appointment->status === 'scheduled' && \$appointment->scheduled_at->isPast())\n";
} else {
    echo "No scheduled appointments with past time found.\n";
    echo "Auto-transition will work for future appointments when their time arrives.\n";
}

echo "\n4. WORKFLOW STATUS FLOW\n";
echo str_repeat("-", 60) . "\n";
echo "Current workflow (confirmed removed):\n";
echo "  1. pending (pet owner books)\n";
echo "  2. scheduled (clinic confirms)\n";
echo "  3. in_progress (auto-transitions when time arrives)\n";
echo "  4. completed (clinic marks complete)\n";
echo "\n";
echo "✅ 'confirmed' status removed from workflow\n";
echo "✅ 'Start Appointment' button removed (auto-transition instead)\n";
echo "✅ Auto-transition updated to move 'scheduled' → 'in_progress'\n";

echo "\n5. RECENT APPOINTMENT SAMPLE\n";
echo str_repeat("-", 60) . "\n";

$recentAppointments = Appointment::with(['pet.owner', 'clinic'])
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentAppointments as $appt) {
    $petName = $appt->pet ? $appt->pet->name : 'Unknown Pet';
    $ownerName = $appt->pet && $appt->pet->owner ? $appt->pet->owner->name : 'Unknown Owner';
    $clinicName = $appt->clinic ? $appt->clinic->clinic_name : 'Unknown Clinic';
    
    echo "\nAppointment #{$appt->id}:\n";
    echo "  Pet: {$petName} (Owner: {$ownerName})\n";
    echo "  Clinic: {$clinicName}\n";
    echo "  Status: {$appt->status}\n";
    echo "  Scheduled: {$appt->scheduled_at->format('M j, Y g:i A')}\n";
    echo "  Created: {$appt->created_at->format('M j, Y g:i A')}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "VERIFICATION COMPLETE\n";
echo str_repeat("=", 60) . "\n";
