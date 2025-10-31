<?php

require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\ClinicRegistration;

echo "Checking clinic data in database...\n";

$clinics = ClinicRegistration::all();
echo "Total clinics: " . $clinics->count() . "\n\n";

// Get all services
$allServices = [];
foreach($clinics as $clinic) {
    if($clinic->services && is_array($clinic->services)) {
        $allServices = array_merge($allServices, $clinic->services);
    }
}

$uniqueServices = array_unique($allServices);
sort($uniqueServices);

echo "Available services in database:\n";
foreach($uniqueServices as $service) {
    echo "- " . $service . "\n";
}

echo "\n===================\n";

// Check if there are any category fields
$firstClinic = $clinics->first();
if($firstClinic) {
    echo "Sample clinic data structure:\n";
    echo "Clinic name: " . $firstClinic->clinic_name . "\n";
    echo "Services: " . implode(', ', $firstClinic->services ?? []) . "\n";
    
    // Check if there's any category field
    $attributes = $firstClinic->getAttributes();
    if(isset($attributes['category'])) {
        echo "Category field exists: " . $attributes['category'] . "\n";
    } else {
        echo "No 'category' field found in clinic registration\n";
    }
}