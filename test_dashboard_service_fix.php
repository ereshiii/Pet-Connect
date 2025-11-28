<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\DashboardStatsService;

echo "Testing DashboardStatsService with fixed service name...\n\n";

$user = User::find(48); // User from screenshot
if (!$user) {
    echo "User not found!\n";
    exit;
}

$service = new DashboardStatsService();
$upcoming = $service->getUpcomingAppointments($user, 5);

echo "Upcoming appointments:\n";
foreach ($upcoming as $appointment) {
    echo "  Pet: {$appointment['pet']['name']}\n";
    echo "  Service Type: {$appointment['type']}\n";
    echo "  Duration: {$appointment['duration']}\n";
    echo "  Status: {$appointment['status']}\n";
    echo "---\n";
}
