<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ClinicRegistration;
use App\Models\ClinicOperatingHour;

echo "Checking MM's Clinic Operating Hours Data\n";
echo str_repeat('=', 70) . "\n\n";

$clinic = ClinicRegistration::find(32);
$clinicId = $clinic->clinic_id ?? $clinic->id;

echo "Clinic ID for operating hours: $clinicId\n\n";

echo "Data in clinic_operating_hours table:\n";
echo "-------------------------------------\n";
$hours = ClinicOperatingHour::where('clinic_id', $clinicId)->orderBy('day_of_week')->get();

foreach ($hours as $hour) {
    echo sprintf(
        "%-12s | open: %-8s | close: %-8s | is_closed: %s\n",
        $hour->day_of_week,
        $hour->opening_time ?? 'NULL',
        $hour->closing_time ?? 'NULL',
        $hour->is_closed ? 'YES' : 'NO'
    );
}

echo "\n";
echo "What the edit() method would return:\n";
echo "------------------------------------\n";

$daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
foreach ($daysOfWeek as $day) {
    $dayHours = $hours->where('day_of_week', $day)->first();
    $result = [
        'open' => $dayHours->opening_time ?? '09:00',
        'close' => $dayHours->closing_time ?? '17:00',
        'is_closed' => $dayHours ? $dayHours->is_closed : false,
    ];
    
    $display = $result['is_closed'] ? 'CLOSED' : "{$result['open']} - {$result['close']}";
    echo sprintf("%-12s | %s\n", $day, $display);
}
