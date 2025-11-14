<?php

// Temporary debug file - run with: php debug_patient_records.php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test the same logic as the controller
$pet = App\Models\Pet::with([
    'owner',
    'owner.profile',
    'type',
    'breed',
    'appointments' => function ($q) {
        $q->where('clinic_id', 1)
          ->orderBy('scheduled_at', 'desc');
    },
    'medicalRecords' => function ($q) {
        $q->where('clinic_id', 1)
          ->orderBy('date', 'desc');
    },
])->find(1);

if (!$pet) {
    echo "Pet not found\n";
    exit;
}

echo "Pet: {$pet->name}\n";
echo "Appointments count: " . $pet->appointments->count() . "\n";
echo "Medical records count: " . $pet->medicalRecords->count() . "\n";

if ($pet->medicalRecords->count() > 0) {
    echo "\nMedical Records:\n";
    foreach ($pet->medicalRecords as $record) {
        echo "- ID: {$record->id}, Title: {$record->title}, Type: {$record->record_type}\n";
    }
    
    // Test transformation
    echo "\nTransformed medical records:\n";
    $medicalRecords = $pet->medicalRecords->map(function ($record) {
        return [
            'id' => $record->id,
            'date' => $record->date->format('Y-m-d'),
            'formatted_date' => $record->date->format('M j, Y'),
            'type' => $record->record_type,
            'title' => $record->title,
            'description' => $record->description,
            'treatment' => $record->instructions,
            'medication' => $record->medication,
            'cost' => $record->cost,
            'formatted_cost' => $record->getCostFormattedAttribute(),
            'veterinarian' => $record->veterinarian ? $record->veterinarian->name : 'Dr. Staff',
            'follow_up_date' => $record->follow_up_date?->format('Y-m-d'),
        ];
    });
    
    echo json_encode($medicalRecords->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "\nNo medical records found\n";
}