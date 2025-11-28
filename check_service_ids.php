<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use App\Models\ClinicService;

echo "Checking service ID mismatches...\n\n";

$appointments = Appointment::latest()->take(5)->get();

echo "Recent appointments service_ids:\n";
foreach ($appointments as $appointment) {
    echo "  Appointment {$appointment->id}: service_id = {$appointment->service_id}\n";
}

echo "\nSample ClinicService records:\n";
$services = ClinicService::take(10)->get();
foreach ($services as $service) {
    echo "  ID: {$service->id} - {$service->service_name} (Clinic: {$service->clinic_registration_id})\n";
}

echo "\nChecking if service_ids from appointments exist in clinic_services:\n";
foreach ($appointments as $appointment) {
    $serviceExists = ClinicService::find($appointment->service_id);
    $status = $serviceExists ? "EXISTS" : "NOT FOUND";
    echo "  service_id {$appointment->service_id}: {$status}\n";
}
