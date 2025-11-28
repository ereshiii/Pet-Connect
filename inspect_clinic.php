<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get first clinic with all data
$clinic = App\Models\ClinicRegistration::first();

if (!$clinic) {
    echo "No clinics found\n";
    exit(1);
}

echo "Clinic data:\n";
echo json_encode($clinic->toArray(), JSON_PRETTY_PRINT);

echo "\n\nServices:\n";
$services = $clinic->clinicServices()->get();
foreach ($services as $service) {
    echo "- {$service->name} ({$service->category}) - {$service->duration_minutes} min\n";
}
