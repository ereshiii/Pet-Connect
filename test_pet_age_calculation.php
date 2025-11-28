<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pet;

$pet = Pet::find(48); // Sigurd from appointment 164

if (!$pet) {
    $pet = Pet::whereNotNull('birth_date')->first();
}

echo "Testing Pet Model Age Calculation\n\n";
echo "Pet ID: {$pet->id}\n";
echo "Name: {$pet->name}\n";
echo "Birth Date: {$pet->birth_date}\n\n";

echo "Direct method call:\n";
$directAge = $pet->getCalculatedAgeAttribute();
echo "getCalculatedAgeAttribute(): {$directAge}\n";
echo "Type: " . gettype($directAge) . "\n\n";

echo "Via attribute access:\n";
$attrAge = $pet->calculated_age;
echo "\$pet->calculated_age: {$attrAge}\n";
echo "Type: " . gettype($attrAge) . "\n\n";

echo "Manual calculation:\n";
$age = $pet->birth_date->diffInYears(now());
$months = $pet->birth_date->diffInMonths(now()) % 12;
echo "Years: {$age}\n";
echo "Months: {$months}\n";

$ageStr = $age . ' year' . ($age != 1 ? 's' : '');
if ($months > 0) {
    $ageStr .= ' and ' . $months . ' month' . ($months != 1 ? 's' : '');
}
$ageStr .= ' old';
echo "Manual result: {$ageStr}\n";
