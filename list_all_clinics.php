<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$clinics = App\Models\ClinicRegistration::all(['id', 'name']);

echo "All Clinics:\n";
foreach ($clinics as $clinic) {
    $serviceCount = $clinic->clinicServices()->count();
    echo "ID: {$clinic->id} - {$clinic->name} - Services: {$serviceCount}\n";
}
