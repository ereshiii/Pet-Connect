<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ClinicService;

echo "Checking ClinicService records with all fields...\n\n";

$services = ClinicService::whereIn('id', [324, 325, 167, 168])->get();

foreach ($services as $service) {
    echo "Service ID: {$service->id}\n";
    echo "  All attributes: " . json_encode($service->getAttributes(), JSON_PRETTY_PRINT) . "\n";
    echo "---\n";
}
