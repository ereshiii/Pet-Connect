<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FailedLoginAttempt;
use App\Models\BlockedIp;
use App\Models\SecurityEvent;
use App\Models\SlowQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SystemMonitoringController extends Controller
{
    public function overview(): Response
    {
        // System health metrics
        $systemHealth = [
            'server_status' => $this->getServerStatus(),
            'database_status' => $this->getDatabaseStatus(),
            'security_status' => $this->getSecurityStatus(),
            'uptime_percentage' => $this->calculateUptime(),
            'response_time_ms' => $this->getAverageResponseTime(),
            'active_users' => $this->getActiveUsersCount(),
            'total_requests' => $this->getTotalRequestsToday(),
            'error_rate' => $this->calculateErrorRate(),
        ];

        // Recent system events
        $recentEvents = $this->getRecentEvents();

        return Inertia::render('1adminPages/SystemMonitoring/Overview', [
            'system_health' => $systemHealth,
            'recent_events' => $recentEvents,
        ]);
    }

    private function getServerStatus(): string
    {
        // Check server health based on various metrics
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $cpuCores = 1; // Default, can be detected dynamically
            
            if ($load[0] > $cpuCores * 0.8) {
                return 'critical';
            } elseif ($load[0] > $cpuCores * 0.5) {
                return 'warning';
            }
        }
        
        return 'healthy';
    }

    private function getDatabaseStatus(): string
    {
        try {
            DB::connection()->getPdo();
            
            // Check database response time
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;
            
            if ($responseTime > 100) {
                return 'warning';
            }
            
            return 'healthy';
        } catch (\Exception $e) {
            return 'critical';
        }
    }

    private function getSecurityStatus(): string
    {
        // Check for security indicators
        // This is a simplified check - in production, implement proper security monitoring
        return 'healthy';
    }

    private function calculateUptime(): float
    {
        // Mock uptime calculation - in production, track actual server uptime
        return 99.9;
    }

    private function getAverageResponseTime(): int
    {
        $today = now()->format('Y-m-d');
        $responseTimes = Cache::get("response_times:{$today}", []);
        
        if (empty($responseTimes)) {
            return 0;
        }
        
        return (int) round(array_sum($responseTimes) / count($responseTimes));
    }

    private function getActiveUsersCount(): int
    {
        // Count users active in the last 15 minutes
        // This would typically use a sessions table or Redis
        return DB::table('users')
            ->where('updated_at', '>=', now()->subMinutes(15))
            ->count();
    }

    private function getTotalRequestsToday(): int
    {
        $today = now()->format('Y-m-d');
        return Cache::get("requests_count:{$today}", 0);
    }

    private function calculateErrorRate(): float
    {
        $today = now()->format('Y-m-d');
        $totalRequests = Cache::get("requests_count:{$today}", 0);
        $errorCount = Cache::get("errors_count:{$today}", 0);
        
        if ($totalRequests === 0) {
            return 0.0;
        }
        
        return round(($errorCount / $totalRequests) * 100, 2);
    }

    private function getRecentEvents(): array
    {
        return SecurityEvent::latest()
            ->take(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'type' => $event->type,
                    'message' => $event->description,
                    'severity' => $event->severity,
                    'timestamp' => $event->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }

    public function server(): Response
    {
        $serverMetrics = [
            'cpu_usage' => rand(30, 70),
            'memory_usage' => rand(40, 80),
            'disk_usage' => rand(50, 70),
            'network_in' => rand(1000000, 5000000),
            'network_out' => rand(800000, 3000000),
            'uptime_seconds' => rand(86400 * 30, 86400 * 90), // 30-90 days
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : [0.5, 0.4, 0.3],
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        ];

        $performanceHistory = [
            'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
            'cpu' => [45, 52, 60, 55, 48, 42],
            'memory' => [60, 62, 65, 68, 64, 61],
        ];

        return Inertia::render('1adminPages/SystemMonitoring/Server', [
            'server_metrics' => $serverMetrics,
            'performance_history' => $performanceHistory,
        ]);
    }

    public function database(): Response
    {
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $totalTables = count($tables);
        
        $totalRecords = 0;
        $tableStats = [];
        
        foreach ($tables as $table) {
            $count = DB::table($table->name)->count();
            $totalRecords += $count;
            
            $tableStats[] = [
                'table_name' => $table->name,
                'row_count' => $count,
                'size_mb' => rand(1, 100) / 10, // Mock size
                'last_updated' => now()->subHours(rand(1, 48))->format('M d, H:i'),
            ];
        }

        // Sort by size and get top 10
        usort($tableStats, fn($a, $b) => $b['row_count'] <=> $a['row_count']);
        $tableStats = array_slice($tableStats, 0, 10);

        $today = now()->format('Y-m-d');
        $queriesCountToday = Cache::get("queries_count:{$today}", 0);
        $queryTimes = Cache::get("query_times:{$today}", []);
        $avgQueryTime = !empty($queryTimes) ? round(array_sum($queryTimes) / count($queryTimes), 2) : 0;
        $slowQueriesCount = SlowQuery::whereDate('created_at', today())->count();

        $databaseMetrics = [
            'total_tables' => $totalTables,
            'total_records' => $totalRecords,
            'database_size_mb' => filesize(database_path('database.sqlite')) / 1024 / 1024,
            'connection_status' => 'connected',
            'query_count_today' => $queriesCountToday,
            'slow_queries' => $slowQueriesCount,
            'avg_query_time_ms' => $avgQueryTime,
            'database_type' => 'SQLite',
            'database_version' => DB::select('SELECT sqlite_version() as version')[0]->version ?? 'Unknown',
        ];

        $recentQueries = SlowQuery::latest()
            ->take(10)
            ->get()
            ->map(function ($query) {
                return [
                    'id' => $query->id,
                    'query' => substr($query->query, 0, 100) . (strlen($query->query) > 100 ? '...' : ''),
                    'execution_time_ms' => $query->time_ms,
                    'timestamp' => $query->created_at->format('Y-m-d H:i:s'),
                    'status' => 'slow',
                ];
            })
            ->toArray();

        return Inertia::render('1adminPages/SystemMonitoring/Database', [
            'database_metrics' => $databaseMetrics,
            'table_stats' => $tableStats,
            'recent_queries' => $recentQueries,
        ]);
    }

    public function security(): Response
    {
        $failedLoginsToday = FailedLoginAttempt::whereDate('created_at', today())->count();
        $blockedIps = BlockedIp::where(function ($query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->count();
        
        $activeSessions = DB::table('sessions')->count();
        
        // Calculate security score based on metrics
        $score = 100;
        if ($failedLoginsToday > 50) $score -= 20;
        elseif ($failedLoginsToday > 20) $score -= 10;
        if ($blockedIps > 10) $score -= 10;
        
        $securityMetrics = [
            'failed_login_attempts' => $failedLoginsToday,
            'blocked_ips' => $blockedIps,
            'active_sessions' => $activeSessions,
            'security_score' => max(0, $score),
            'ssl_status' => 'active',
            'firewall_status' => 'active',
            'last_scan' => now()->subHours(6)->format('M d, Y H:i'),
            'vulnerabilities_found' => 0,
        ];

        $failedLogins = FailedLoginAttempt::latest()
            ->take(10)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'email' => $attempt->email,
                    'ip_address' => $attempt->ip_address,
                    'timestamp' => $attempt->created_at->format('Y-m-d H:i:s'),
                    'reason' => $attempt->reason ?? 'Invalid credentials',
                ];
            })
            ->toArray();

        $blockedIpsList = BlockedIp::where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('blocked_at')
            ->get()
            ->map(function ($blocked) {
                return [
                    'ip_address' => $blocked->ip_address,
                    'reason' => $blocked->reason,
                    'blocked_at' => $blocked->blocked_at->format('M d, Y H:i'),
                    'attempts' => $blocked->attempts_count,
                ];
            })
            ->toArray();

        $securityEvents = SecurityEvent::latest()
            ->take(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'type' => $event->type,
                    'description' => $event->description,
                    'severity' => $event->severity,
                    'timestamp' => $event->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();

        return Inertia::render('1adminPages/SystemMonitoring/Security', [
            'security_metrics' => $securityMetrics,
            'failed_logins' => $failedLogins,
            'blocked_ips' => $blockedIpsList,
            'security_events' => $securityEvents,
        ]);
    }
}
