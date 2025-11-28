<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pet;
use Illuminate\Support\Facades\DB;

echo "=== PET BREED INVESTIGATION ===\n\n";

// Check raw database
echo "--- RAW DATABASE VALUES ---\n";
$rawPets = DB::table('pets')->limit(5)->get(['id', 'name', 'breed_id', 'breed', 'type_id']);
foreach ($rawPets as $pet) {
    echo "ID {$pet->id}: {$pet->name}\n";
    echo "  breed_id: " . ($pet->breed_id ?? 'NULL') . "\n";
    echo "  breed (string): " . ($pet->breed ?? 'NULL') . "\n";
    echo "  type_id: " . ($pet->type_id ?? 'NULL') . "\n\n";
}

// Check Eloquent model
echo "\n--- ELOQUENT MODEL (with breed relationship) ---\n";
$pets = Pet::with('breed')->limit(3)->get();
foreach ($pets as $pet) {
    echo "ID {$pet->id}: {$pet->name}\n";
    echo "  breed_id: " . ($pet->breed_id ?? 'NULL') . "\n";
    echo "  breed (attribute): " . ($pet->getAttributeValue('breed') ?? 'NULL') . "\n";
    echo "  breed (relationship): " . ($pet->breed ? $pet->breed->name : 'NULL') . "\n";
    echo "  Has relationship loaded: " . ($pet->relationLoaded('breed') ? 'YES' : 'NO') . "\n\n";
}

// Check pet_breeds table
echo "\n--- PET BREEDS TABLE ---\n";
$breeds = DB::table('pet_breeds')->limit(5)->get(['id', 'name', 'species']);
foreach ($breeds as $breed) {
    echo "ID {$breed->id}: {$breed->name} ({$breed->species})\n";
}
