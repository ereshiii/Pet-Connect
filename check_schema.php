<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== APPOINTMENTS TABLE SCHEMA ===\n\n";

$columns = Schema::getColumnListing('appointments');

echo "Columns in appointments table:\n";
foreach ($columns as $column) {
    echo "  - $column\n";
}

echo "\n=== SAMPLE APPOINTMENT DATA ===\n\n";

$apt = DB::table('appointments')->where('id', 77)->first();

if ($apt) {
    echo "Raw object dump for ID 77:\n";
    var_dump($apt);
}
