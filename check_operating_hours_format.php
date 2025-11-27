<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ClinicRegistration;
use Carbon\Carbon;

echo "Checking Operating Hours Format\n";
echo str_repeat('=', 70) . "\n\n";

// Check a few clinics
$clinics = ClinicRegistration::where('status', 'approved')
    ->where('clinic_name', 'like', '%MM%')
    ->orWhere('clinic_name', 'like', '%Amor%')
    ->get();

echo "Found " . $clinics->count() . " clinics\n\n";

foreach ($clinics as $clinic) {
    echo "Clinic: {$clinic->clinic_name}\n";
    echo "ID: {$clinic->id}\n";
    
    $operatingHours = $clinic->operating_hours;
    echo "Operating Hours Type: " . gettype($operatingHours) . "\n";
    
    if (is_array($operatingHours)) {
        echo "Operating Hours Data:\n";
        foreach ($operatingHours as $day => $hours) {
            if (is_array($hours)) {
                $open = $hours['open'] ?? $hours['opening_time'] ?? 'N/A';
                $close = $hours['close'] ?? $hours['closing_time'] ?? 'N/A';
                $closed = isset($hours['closed']) ? 'CLOSED' : '';
                echo "  $day: open='$open', close='$close' $closed\n";
            } else {
                echo "  $day: " . json_encode($hours) . "\n";
            }
        }
    } else {
        echo "Operating Hours: " . json_encode($operatingHours) . "\n";
    }
    
    // Test current status
    echo "\nCurrent Status Check:\n";
    $now = Carbon::now('Asia/Manila');
    echo "  Server time: " . $now->format('Y-m-d H:i:s') . "\n";
    echo "  Day: " . strtolower($now->format('l')) . "\n";
    echo "  Time (H:i): " . $now->format('H:i') . "\n";
    
    if (is_array($operatingHours)) {
        $currentDay = strtolower($now->format('l'));
        if (isset($operatingHours[$currentDay])) {
            $todayHours = $operatingHours[$currentDay];
            if (is_array($todayHours)) {
                $open = $todayHours['open'] ?? $todayHours['opening_time'] ?? null;
                $close = $todayHours['close'] ?? $todayHours['closing_time'] ?? null;
                $currentTime = $now->format('H:i');
                
                echo "  Today's hours: $open - $close\n";
                echo "  Comparison: $currentTime >= $open && $currentTime <= $close\n";
                echo "  Is open: " . ($currentTime >= $open && $currentTime <= $close ? 'YES' : 'NO') . "\n";
            }
        }
    }
    
    echo "\n" . str_repeat('-', 70) . "\n\n";
}
