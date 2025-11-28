<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;

echo "Checking appointments and their services...\n\n";

$appointments = Appointment::with('service', 'pet')->latest()->take(5)->get();

foreach ($appointments as $appointment) {
    echo "Appointment ID: {$appointment->id}\n";
    echo "  Pet: {$appointment->pet->name}\n";
    echo "  Service ID: " . ($appointment->service_id ?? 'NULL') . "\n";
    echo "  Service Name: " . ($appointment->service?->service_name ?? 'NULL') . "\n";
    echo "  Type field: " . ($appointment->type ?? 'NULL') . "\n";
    echo "  Scheduled: {$appointment->scheduled_at}\n";
    echo "---\n";
}
