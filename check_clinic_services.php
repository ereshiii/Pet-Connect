<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$clinic = App\Models\ClinicRegistration::where('name', 'LIKE', '%Furfect%')->first();

if (!$clinic) {
    echo "Clinic not found\n";
    exit(1);
}

echo "Clinic ID: {$clinic->id}\n";
echo "Clinic Name: {$clinic->name}\n\n";

$services = $clinic->clinicServices()->get();
echo "Total Services: " . $services->count() . "\n\n";

foreach ($services as $service) {
    echo "ID: {$service->id}\n";
    echo "Name: {$service->name}\n";
    echo "Category: {$service->category}\n";
    echo "Duration: {$service->duration_minutes} minutes\n";
    echo "Description: " . ($service->description ?: 'N/A') . "\n";
    echo "---\n";
}
