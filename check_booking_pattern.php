<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== CHECKING RECENT APPOINTMENTS FOR DATE PATTERN ===\n\n";

$appointments = Appointment::orderBy('id', 'desc')
    ->limit(10)
    ->get();

foreach ($appointments as $apt) {
    $scheduled = Carbon::parse($apt->scheduled_at);
    $created = Carbon::parse($apt->created_at);
    
    echo "ID {$apt->id}:\n";
    echo "  Created: {$created->format('Y-m-d H:i:s T')}\n";
    echo "  Scheduled: {$scheduled->format('Y-m-d H:i:s T')}\n";
    echo "  Display date: {$scheduled->format('F j, Y')}\n";
    echo "  Display time: {$scheduled->format('g:i A')}\n";
    
    // Check if the dates differ
    if ($scheduled->format('Y-m-d') !== $created->format('Y-m-d')) {
        echo "  ⚠️ Scheduled date differs from creation date\n";
    }
    
    echo "\n";
}

echo "\n=== CHECK FORM SUBMISSION LOGS ===\n";
echo "Look for 'Booking date/time input' and 'Booking date/time parsed' in Laravel logs\n";
echo "Run: tail -n 100 storage/logs/laravel.log | grep -A 5 'Booking date'\n";
