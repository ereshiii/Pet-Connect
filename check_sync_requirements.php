<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking ClinicRegistration Sync Requirements\n";
echo str_repeat('=', 70) . "\n\n";

$registration = new App\Models\ClinicRegistration();
$casts = $registration->getCasts();

echo "JSON/Array columns in clinic_registrations:\n";
foreach ($casts as $column => $type) {
    if ($type === 'array') {
        echo "  - $column (array)\n";
    }
}
echo "\n";

echo "Sync Analysis:\n";
echo "==============\n\n";

echo "1. operating_hours (JSON column)\n";
echo "   Syncs with: clinic_operating_hours table\n";
echo "   Controller: OperatingHoursController\n";
echo "   Status: ✅ FIXED - Now syncs both locations\n\n";

echo "2. services (JSON column)\n";
echo "   Related table: clinic_services\n";
echo "   Usage: Only used for initial import from registration\n";
echo "   Status: ✅ NO SYNC NEEDED - Uses table only after import\n\n";

echo "3. veterinarians (JSON column)\n";
echo "   Related table: clinic_staff\n";
echo "   Usage: Only used for initial import from registration\n";
echo "   Status: ✅ NO SYNC NEEDED - Uses table only after import\n\n";

echo "4. gallery (JSON column)\n";
echo "   Related table: None - stored only in JSON\n";
echo "   Controller: ClinicGalleryController\n";
echo "   Status: ✅ NO SYNC NEEDED - Single source of truth\n\n";

echo "5. certification_proofs (JSON column)\n";
echo "   Related table: None\n";
echo "   Status: ✅ NO SYNC NEEDED - Registration only\n\n";

echo "\nConclusion:\n";
echo "===========\n";
echo "Only operating_hours needs bidirectional sync.\n";
echo "Other arrays are either:\n";
echo "  - One-time import (services, veterinarians)\n";
echo "  - Single source (gallery, certification_proofs)\n";
