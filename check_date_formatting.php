<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== DATE FORMATTING INVESTIGATION ===\n\n";

// Find an appointment from Nov 27
$appointment = Appointment::whereDate('appointment_date', '2024-11-27')
    ->first();

if ($appointment) {
    echo "Found appointment ID: {$appointment->id}\n";
    echo "Owner: {$appointment->petOwner->first_name} {$appointment->petOwner->last_name}\n";
    echo "Pet: {$appointment->pet->name}\n\n";
    
    echo "=== RAW DATABASE VALUES ===\n";
    echo "appointment_date (raw): {$appointment->getRawOriginal('appointment_date')}\n";
    echo "appointment_time (raw): {$appointment->getRawOriginal('appointment_time')}\n\n";
    
    echo "=== CARBON FORMATTED VALUES ===\n";
    echo "appointment_date (Carbon): {$appointment->appointment_date}\n";
    echo "appointment_time (Carbon): {$appointment->appointment_time}\n\n";
    
    echo "=== FORMATTED FOR FRONTEND (Y-m-d) ===\n";
    echo "appointment_date->format('Y-m-d'): " . Carbon::parse($appointment->appointment_date)->format('Y-m-d') . "\n";
    echo "appointment_date->toDateString(): " . Carbon::parse($appointment->appointment_date)->toDateString() . "\n\n";
    
    echo "=== TIMEZONE INFO ===\n";
    echo "App timezone: " . config('app.timezone') . "\n";
    echo "Date with timezone: " . Carbon::parse($appointment->appointment_date)->timezone(config('app.timezone'))->format('Y-m-d H:i:s T') . "\n\n";
    
    echo "=== CONTROLLER TRANSFORMATION ===\n";
    $transformed = [
        'date' => Carbon::parse($appointment->appointment_date)->format('Y-m-d'),
        'time' => Carbon::parse($appointment->appointment_time)->format('H:i'),
    ];
    echo "Transformed date: {$transformed['date']}\n";
    echo "Transformed time: {$transformed['time']}\n\n";
    
} else {
    echo "No appointment found for Nov 27, 2024\n\n";
    echo "Let's check what dates we have:\n";
    $dates = Appointment::select('appointment_date')
        ->distinct()
        ->orderBy('appointment_date')
        ->limit(10)
        ->get();
    
    foreach ($dates as $apt) {
        echo "- {$apt->appointment_date}\n";
    }
}

echo "\n=== CHECKING ALL UPCOMING APPOINTMENTS ===\n";
$upcoming = Appointment::whereIn('status', ['scheduled', 'pending', 'confirmed', 'in_progress'])
    ->where('appointment_date', '>=', now()->toDateString())
    ->orderBy('appointment_date')
    ->limit(5)
    ->get();

foreach ($upcoming as $apt) {
    $raw = $apt->getRawOriginal('appointment_date');
    $formatted = Carbon::parse($apt->appointment_date)->format('Y-m-d');
    echo "ID {$apt->id}: Raw='{$raw}' | Formatted='{$formatted}' | Status={$apt->status}\n";
}
