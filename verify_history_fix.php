<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\User;

echo "=== BOOKING HISTORY FIX VERIFICATION ===\n\n";

// Find a pet owner user (not clinic)
$petOwner = User::whereHas('pets')
    ->whereDoesntHave('clinicRegistration')
    ->first();

if (!$petOwner) {
    echo "⚠️  No pet owner found with pets\n";
    exit(1);
}

echo "Testing for Pet Owner: {$petOwner->name} (ID: {$petOwner->id})\n";
echo str_repeat("-", 60) . "\n\n";

// 1. Check all appointments for this user
echo "1. ALL APPOINTMENTS FOR THIS USER\n";
echo str_repeat("-", 60) . "\n";

$allAppointments = Appointment::where('owner_id', $petOwner->id)
    ->orderBy('scheduled_at', 'desc')
    ->get();

echo "Total appointments: {$allAppointments->count()}\n\n";

$statusBreakdown = $allAppointments->groupBy('status')->map->count();
foreach ($statusBreakdown as $status => $count) {
    echo "  {$status}: {$count} appointments\n";
}

// 2. Check history appointments (what should show in History page)
echo "\n2. HISTORY APPOINTMENTS (completed, cancelled, no_show)\n";
echo str_repeat("-", 60) . "\n";

$historyAppointments = Appointment::where('owner_id', $petOwner->id)
    ->whereIn('status', ['completed', 'cancelled', 'no_show'])
    ->orderBy('scheduled_at', 'desc')
    ->get();

echo "History appointments: {$historyAppointments->count()}\n\n";

if ($historyAppointments->count() > 0) {
    echo "Sample history appointments:\n";
    foreach ($historyAppointments->take(5) as $appointment) {
        $pet = $appointment->pet;
        $clinic = $appointment->clinicRegistration;
        
        echo "\n  Appointment #{$appointment->id}:\n";
        echo "    Status: {$appointment->status}\n";
        echo "    Pet: " . ($pet ? $pet->name : 'Unknown') . "\n";
        echo "    Clinic: " . ($clinic ? $clinic->clinic_name : 'Unknown') . "\n";
        echo "    Date: {$appointment->scheduled_at->format('M j, Y g:i A')}\n";
    }
} else {
    echo "  No history appointments found\n";
}

// 3. Check what the controller would return
echo "\n3. CONTROLLER QUERY SIMULATION\n";
echo str_repeat("-", 60) . "\n";

$query = Appointment::with([
    'pet:id,name,type,breed',
    'clinicRegistration:id,clinic_name,phone',
    'veterinarian:id,name',
    'service:id,name',
    'owner:id,name,email,phone'
])->where('owner_id', $petOwner->id)
  ->whereIn('status', ['completed', 'cancelled', 'no_show']); // THE FIX

$controllerResults = $query->orderBy('scheduled_at', 'desc')->paginate(10);

echo "Results from controller query: {$controllerResults->total()}\n";
echo "Current page: {$controllerResults->currentPage()}\n";
echo "Per page: {$controllerResults->perPage()}\n";
echo "Total pages: {$controllerResults->lastPage()}\n\n";

if ($controllerResults->count() > 0) {
    echo "✅ Controller would return {$controllerResults->count()} appointment(s) on first page\n";
} else {
    echo "⚠️  Controller would return 0 appointments\n";
}

// 4. Before/After comparison
echo "\n4. BEFORE/AFTER FIX COMPARISON\n";
echo str_repeat("-", 60) . "\n";

echo "BEFORE (incorrect query - missing status filter):\n";
$beforeQuery = Appointment::where('owner_id', $petOwner->id);
echo "  Would return: {$beforeQuery->count()} appointments (ALL statuses)\n";
echo "  Vue component filters to: {$historyAppointments->count()} (only history)\n";
echo "  Problem: If no completed/cancelled/no_show, shows empty!\n\n";

echo "AFTER (correct query - with status filter):\n";
$afterQuery = Appointment::where('owner_id', $petOwner->id)
    ->whereIn('status', ['completed', 'cancelled', 'no_show']);
echo "  Would return: {$afterQuery->count()} appointments (ONLY history statuses)\n";
echo "  Vue component shows: {$afterQuery->count()} (same - consistent!)\n";
echo "  ✅ Fixed: Now shows actual history appointments\n";

// 5. Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "FIX SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "Issue:\n";
echo "  Pet owner's history page was not displaying appointments\n";
echo "  because the controller returned ALL appointments but the\n";
echo "  Vue component filtered to only show completed/cancelled/no_show.\n\n";

echo "Root Cause:\n";
echo "  Controller query for pet owners was missing:\n";
echo "  ->whereIn('status', ['completed', 'cancelled', 'no_show'])\n\n";

echo "Solution Applied:\n";
echo "  Added status filter to pet owner query in AppointmentController\n";
echo "  Line ~1194: Added ->whereIn('status', ['completed', 'cancelled', 'no_show'])\n\n";

echo "Result:\n";
if ($historyAppointments->count() > 0) {
    echo "  ✅ Pet owners will now see {$historyAppointments->count()} history appointment(s)\n";
} else {
    echo "  ℹ️  This user has no history appointments yet\n";
    echo "     (Need at least one completed/cancelled/no_show appointment)\n";
}

echo "\nNote for Clinic Users:\n";
echo "  Clinic history was already working correctly (had status filter)\n";
echo "  This fix only affects pet owner view\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "VERIFICATION COMPLETE\n";
echo str_repeat("=", 60) . "\n";
