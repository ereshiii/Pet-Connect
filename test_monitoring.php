<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Monitoring System ===\n\n";

// Test 1: Add failed login attempts
echo "1. Adding test failed login attempts...\n";
for ($i = 0; $i < 3; $i++) {
    DB::table('failed_login_attempts')->insert([
        'email' => 'test' . $i . '@example.com',
        'ip_address' => '192.168.1.' . (100 + $i),
        'user_agent' => 'Test Browser',
        'reason' => 'Invalid credentials',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "   ✓ Added 3 failed login attempts\n\n";

// Test 2: Simulate auto-blocking (5 attempts from same IP)
echo "2. Simulating IP auto-blocking (5 attempts from same IP)...\n";
$testIp = '10.0.0.99';
for ($i = 0; $i < 5; $i++) {
    DB::table('failed_login_attempts')->insert([
        'email' => 'attacker@evil.com',
        'ip_address' => $testIp,
        'user_agent' => 'Bot',
        'reason' => 'Invalid credentials',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

// Check recent attempts for this IP
$recentAttempts = DB::table('failed_login_attempts')
    ->where('ip_address', $testIp)
    ->where('created_at', '>', now()->subMinutes(15))
    ->count();

echo "   Recent attempts from {$testIp}: {$recentAttempts}\n";

// Manually trigger block if >= 5
if ($recentAttempts >= 5) {
    $existingBlock = DB::table('blocked_ips')->where('ip_address', $testIp)->first();
    if (!$existingBlock) {
        DB::table('blocked_ips')->insert([
            'ip_address' => $testIp,
            'reason' => 'Too many failed login attempts',
            'attempts_count' => $recentAttempts,
            'blocked_at' => now(),
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('security_events')->insert([
            'type' => 'ip_blocked',
            'description' => "IP {$testIp} blocked due to {$recentAttempts} failed login attempts",
            'severity' => 'high',
            'ip_address' => $testIp,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "   ✓ IP {$testIp} has been blocked\n\n";
    } else {
        echo "   ℹ IP {$testIp} already blocked\n\n";
    }
}

// Test 3: Add request metrics to cache
echo "3. Adding request metrics to cache...\n";
$today = now()->format('Y-m-d');
Cache::put("requests_count:{$today}", 1247, now()->addDay());
Cache::put("errors_count:{$today}", 15, now()->addDay());
Cache::put("response_times:{$today}", [45, 52, 38, 67, 41, 55, 48, 62, 39, 44], now()->addDay());
echo "   ✓ Added request metrics (1247 requests, 15 errors, avg ~49ms)\n\n";

// Test 4: Add query metrics
echo "4. Adding query metrics to cache...\n";
Cache::put("queries_count:{$today}", 8532, now()->addDay());
Cache::put("query_times:{$today}", [12, 15, 8, 45, 11, 23, 9, 102, 18, 7], now()->addDay());
echo "   ✓ Added query metrics (8532 queries, some slow)\n\n";

// Test 5: Add slow query record
echo "5. Adding slow query record...\n";
DB::table('slow_queries')->insert([
    'query' => 'SELECT * FROM clinics WHERE status = ? AND is_featured = ? ORDER BY created_at DESC',
    'bindings' => json_encode(['approved', true]),
    'time_ms' => 156.78,
    'file' => 'app/Http/Controllers/ClinicController.php',
    'line' => 42,
    'created_at' => now(),
    'updated_at' => now(),
]);
echo "   ✓ Added slow query (156.78ms)\n\n";

// Test 6: Display current stats
echo "=== Current Monitoring Stats ===\n";
$stats = [
    'Failed Logins Today' => DB::table('failed_login_attempts')->whereDate('created_at', today())->count(),
    'Blocked IPs' => DB::table('blocked_ips')->count(),
    'Security Events' => DB::table('security_events')->count(),
    'Slow Queries' => DB::table('slow_queries')->count(),
    'Requests Today (cache)' => Cache::get("requests_count:{$today}", 0),
    'Errors Today (cache)' => Cache::get("errors_count:{$today}", 0),
    'Avg Response Time (cache)' => round(array_sum(Cache::get("response_times:{$today}", [0])) / count(Cache::get("response_times:{$today}", [1]))) . 'ms',
    'Queries Today (cache)' => Cache::get("queries_count:{$today}", 0),
];

foreach ($stats as $key => $value) {
    echo str_pad($key . ':', 30) . $value . "\n";
}

echo "\n✅ Monitoring system test complete!\n";
echo "Visit: http://petconnect.test/admin/system-monitoring to see the results\n";
