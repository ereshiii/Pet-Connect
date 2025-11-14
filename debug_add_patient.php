<?php

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Use the models
use App\Models\Pet;
use App\Models\User;
use App\Models\PetMedicalRecord;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG: Add Patient Test Data ===\n\n";

// Test data for adding a patient
$testPatientData = [
    'name' => 'Fluffy',
    'species' => 'cat',
    'breed' => 'Persian',
    'gender' => 'female',
    'age' => 3,
    'weight' => 4.5,
    'color' => 'White',
    'markings' => 'Blue eyes, long fur',
    'microchip_id' => 'CAT123456789',
    'is_neutered' => true,
    'birth_date' => '2021-06-15',
    'special_needs' => 'Requires daily brushing',
    
    // Owner Information
    'owner_name' => 'Maria Santos',
    'owner_email' => 'maria.santos@test.com',
    'owner_phone' => '09171234567',
    'emergency_contact' => 'Juan Santos - 09181234567',
    
    // Medical Information
    'allergies' => 'Seafood, certain grasses',
    'current_medications' => 'Monthly flea prevention',
    'medical_conditions' => 'Mild respiratory sensitivity',
];

echo "Test patient data prepared:\n";
print_r($testPatientData);

// Simulate the controller logic
try {
    DB::beginTransaction();

    // Check if owner exists
    $owner = User::where('email', $testPatientData['owner_email'])->first();
    echo "\nExisting owner check: " . ($owner ? "Found owner: {$owner->name}" : "No existing owner found") . "\n";

    if (!$owner) {
        echo "Creating new owner account...\n";
        
        $owner = User::create([
            'name' => $testPatientData['owner_name'],
            'email' => $testPatientData['owner_email'],
            'password' => bcrypt('temporary_password_' . time()),
            'role' => 'user',
            'email_verified_at' => null,
        ]);

        $owner->profile()->create([
            'phone' => $testPatientData['owner_phone'],
            'emergency_contact_name' => $testPatientData['emergency_contact'],
        ]);
        
        echo "New owner created: {$owner->name} (ID: {$owner->id})\n";
    }

    // Create the pet
    echo "Creating pet record...\n";
    
    $pet = Pet::create([
        'name' => $testPatientData['name'],
        'species' => $testPatientData['species'],
        'breed' => $testPatientData['breed'],
        'gender' => $testPatientData['gender'],
        'age' => $testPatientData['age'],
        'weight' => $testPatientData['weight'],
        'color' => $testPatientData['color'],
        'markings' => $testPatientData['markings'],
        'microchip_id' => $testPatientData['microchip_id'],
        'is_neutered' => $testPatientData['is_neutered'],
        'birth_date' => $testPatientData['birth_date'],
        'special_needs' => $testPatientData['special_needs'],
        'allergies' => $testPatientData['allergies'],
        'current_medications' => $testPatientData['current_medications'],
        'medical_conditions' => $testPatientData['medical_conditions'],
        'owner_id' => $owner->id,
        'is_active' => true,
    ]);
    
    echo "Pet created successfully: {$pet->name} (ID: {$pet->id})\n";

    // Create initial medical record
    echo "Creating initial medical record...\n";
    
    $medicalRecord = PetMedicalRecord::create([
        'pet_id' => $pet->id,
        'clinic_id' => 1, // Assuming clinic ID 1
        'record_type' => 'checkup',
        'title' => 'Initial Clinic Registration',
        'description' => 'Patient manually added to clinic records',
        'date' => now(),
    ]);
    
    echo "Medical record created: ID {$medicalRecord->id}\n";

    DB::commit();
    echo "\n✅ Patient addition test SUCCESSFUL!\n";

    // Verify the creation
    echo "\n=== Verification ===\n";
    $verifyPet = Pet::with('owner', 'owner.profile')->find($pet->id);
    echo "Pet: {$verifyPet->name} ({$verifyPet->species})\n";
    echo "Owner: {$verifyPet->owner->name}\n";
    echo "Phone: {$verifyPet->owner->profile->phone}\n";
    echo "Medical records count: " . $verifyPet->medicalRecords()->count() . "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error occurred: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Current patient count ===\n";
$totalPets = Pet::where('is_active', true)->count();
echo "Total active pets in database: {$totalPets}\n";