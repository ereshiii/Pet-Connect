<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

echo "=== MIGRATING 'CONFIRMED' TO 'SCHEDULED' STATUS ===\n\n";

// Find all confirmed appointments
$confirmedAppointments = Appointment::where('status', 'confirmed')->get();

echo "Found {$confirmedAppointments->count()} appointments with 'confirmed' status\n\n";

if ($confirmedAppointments->isEmpty()) {
    echo "✅ No appointments to migrate.\n";
    exit(0);
}

echo "Migrating appointments...\n";
echo str_repeat("-", 60) . "\n";

$updated = 0;
foreach ($confirmedAppointments as $appointment) {
    echo "Appointment #{$appointment->id}: ";
    echo "confirmed → scheduled\n";
    
    $appointment->update(['status' => 'scheduled']);
    $updated++;
}

echo str_repeat("-", 60) . "\n";
echo "\n✅ Successfully migrated {$updated} appointments\n";
echo "Status changed from 'confirmed' to 'scheduled'\n\n";

// Verify
$remaining = Appointment::where('status', 'confirmed')->count();
echo "Remaining 'confirmed' appointments: {$remaining}\n";

if ($remaining === 0) {
    echo "✅ SUCCESS: All appointments migrated successfully!\n";
} else {
    echo "⚠️  WARNING: {$remaining} appointments still have 'confirmed' status\n";
}

echo "\nNew status distribution:\n";
$statusCounts = \Illuminate\Support\Facades\DB::table('appointments')
    ->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

foreach ($statusCounts as $status) {
    echo "  {$status->status}: {$status->count} appointments\n";
}
