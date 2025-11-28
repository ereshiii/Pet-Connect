<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== RAW DATABASE VALUES ===\n\n";

$appointments = DB::table('appointments')
    ->whereIn('id', [77, 79, 80, 93, 102])
    ->get();

foreach ($appointments as $apt) {
    echo "ID: {$apt->id}\n";
    echo "  appointment_date: {$apt->appointment_date}\n";
    echo "  appointment_time: {$apt->appointment_time}\n";
    echo "  status: {$apt->status}\n\n";
}

echo "\n=== CHECKING RECENT APPOINTMENTS ===\n";
$recent = DB::table('appointments')
    ->where('status', 'confirmed')
    ->orderBy('id', 'desc')
    ->limit(3)
    ->get(['id', 'appointment_date', 'appointment_time', 'status']);

foreach ($recent as $apt) {
    echo "ID {$apt->id}: date={$apt->appointment_date} time={$apt->appointment_time} status={$apt->status}\n";
}
