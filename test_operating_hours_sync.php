<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ClinicRegistration;
use Carbon\Carbon;

echo "Testing Operating Hours Sync Fix\n";
echo str_repeat('=', 70) . "\n\n";

// Find MM's Clinic
$clinic = ClinicRegistration::where('clinic_name', 'like', '%MM%')->first();

if (!$clinic) {
    echo "MM's Clinic not found!\n";
    exit(1);
}

echo "Clinic: {$clinic->clinic_name} (ID: {$clinic->id})\n\n";

echo "Current operating_hours JSON column:\n";
echo "-------------------------------------\n";
$currentHours = $clinic->operating_hours;
if (is_array($currentHours)) {
    foreach ($currentHours as $day => $hours) {
        if (is_array($hours)) {
            if (isset($hours['closed'])) {
                echo "  $day: CLOSED\n";
            } else {
                $open = $hours['open'] ?? 'N/A';
                $close = $hours['close'] ?? 'N/A';
                echo "  $day: $open - $close\n";
            }
        }
    }
} else {
    echo "  Not set or invalid format\n";
}

echo "\n";
echo "Operating Hours from clinic_operating_hours table:\n";
echo "--------------------------------------------------\n";
$tableHours = $clinic->operatingHours()->get();
if ($tableHours->count() > 0) {
    foreach ($tableHours as $hour) {
        $status = $hour->is_closed ? 'CLOSED' : "{$hour->opening_time} - {$hour->closing_time}";
        echo "  {$hour->day_of_week}: $status\n";
    }
} else {
    echo "  No records in table\n";
}

echo "\n";
echo "Current Status Check:\n";
echo "--------------------\n";
$now = Carbon::now('Asia/Manila');
$currentDay = strtolower($now->format('l'));
$currentTime = $now->format('H:i');

echo "  Server time: {$now->format('Y-m-d H:i:s')}\n";
echo "  Current day: $currentDay\n";
echo "  Current time (H:i): $currentTime\n\n";

if (is_array($currentHours) && isset($currentHours[$currentDay])) {
    $todayHours = $currentHours[$currentDay];
    if (isset($todayHours['closed'])) {
        echo "  Status: CLOSED (marked as closed day)\n";
    } elseif (empty($todayHours['open']) || empty($todayHours['close'])) {
        echo "  Status: CLOSED (no hours set)\n";
        echo "  Open: " . ($todayHours['open'] ?? 'null') . "\n";
        echo "  Close: " . ($todayHours['close'] ?? 'null') . "\n";
    } else {
        $open = $todayHours['open'];
        $close = $todayHours['close'];
        $isOpen = $currentTime >= $open && $currentTime <= $close;
        echo "  Hours: $open - $close\n";
        echo "  Status: " . ($isOpen ? "OPEN ✅" : "CLOSED") . "\n";
        echo "  Comparison: $currentTime >= $open && $currentTime <= $close\n";
    }
} else {
    echo "  Status: CLOSED (no data for $currentDay)\n";
}

echo "\n";
echo "Instructions to Fix:\n";
echo "===================\n";
echo "1. Log in as the clinic owner\n";
echo "2. Go to Operating Hours Management\n";
echo "3. Click 'Save' or 'Update' (even without changes)\n";
echo "4. The sync will populate the operating_hours JSON column\n";
