<?php

// Test time comparison logic
$openTime = '06:00';
$closeTime = '17:00';
$currentTime = '07:25';

echo "Time Comparison Test\n";
echo str_repeat('=', 50) . "\n";
echo "Open time: " . $openTime . "\n";
echo "Close time: " . $closeTime . "\n";
echo "Current time: " . $currentTime . "\n\n";

echo "String comparison:\n";
echo "  $currentTime >= $openTime: " . ($currentTime >= $openTime ? 'true' : 'false') . "\n";
echo "  $currentTime <= $closeTime: " . ($currentTime <= $closeTime ? 'true' : 'false') . "\n";
echo "  Result: " . ($currentTime >= $openTime && $currentTime <= $closeTime ? 'OPEN' : 'CLOSED') . "\n\n";

// Test with different formats
echo "Testing different time formats:\n";
$times = [
    ['06:00', '17:00', '07:25', 'Should be OPEN'],
    ['08:00', '18:00', '07:25', 'Should be CLOSED'],
    ['6:00', '17:00', '7:25', 'Missing leading zeros'],
];

foreach ($times as $test) {
    [$open, $close, $current, $desc] = $test;
    $isOpen = $current >= $open && $current <= $close;
    echo "  Open: $open, Close: $close, Current: $current\n";
    echo "  $desc → Result: " . ($isOpen ? 'OPEN' : 'CLOSED') . "\n\n";
}

// Test what MM's Clinic actual data might be
echo "Testing MM's Clinic data:\n";
date_default_timezone_set('Asia/Manila');
$now = new DateTime();
echo "  Current server time: " . $now->format('H:i') . "\n";
echo "  Current day: " . strtolower($now->format('l')) . "\n";

// Simulate clinic hours
$operatingHours = [
    'thursday' => [
        'open' => '06:00',
        'close' => '17:00'
    ]
];

$currentDay = strtolower($now->format('l'));
$currentTime = $now->format('H:i');

if (isset($operatingHours[$currentDay])) {
    $hours = $operatingHours[$currentDay];
    $open = $hours['open'];
    $close = $hours['close'];
    
    echo "  Clinic hours: $open - $close\n";
    echo "  Is open: " . ($currentTime >= $open && $currentTime <= $close ? 'YES' : 'NO') . "\n";
}
