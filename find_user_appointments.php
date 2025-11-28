<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use App\Models\User;

echo "Finding users with appointments...\n\n";

$appointments = Appointment::with('user', 'service', 'pet')
    ->where('scheduled_at', '>', now())
    ->latest()
    ->take(5)
    ->get();

foreach ($appointments as $appointment) {
    echo "User ID: {$appointment->user_id}\n";
    echo "  User Name: {$appointment->user->name}\n";
    echo "  Pet: {$appointment->pet->name}\n";
    echo "  Service ID: {$appointment->service_id}\n";
    echo "  Service Name (raw): " . ($appointment->service->name ?? 'NULL') . "\n";
    echo "  Duration: {$appointment->service->duration_minutes} minutes\n";
    echo "  Scheduled: {$appointment->scheduled_at}\n";
    echo "---\n";
}
