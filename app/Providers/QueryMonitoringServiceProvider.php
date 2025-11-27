<?php

namespace App\Providers;

use App\Models\SlowQuery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class QueryMonitoringServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only enable query monitoring if configured
        if (!config('monitoring.enable_query_logging', false)) {
            return;
        }

        DB::listen(function ($query) {
            // Prevent infinite recursion by not logging queries to monitoring tables
            $monitoringTables = ['slow_queries', 'failed_login_attempts', 'blocked_ips', 'security_events'];
            foreach ($monitoringTables as $table) {
                if (stripos($query->sql, $table) !== false) {
                    return;
                }
            }

            $time = $query->time; // Time in milliseconds
            $threshold = config('monitoring.slow_query_threshold', 100);

            // Track query metrics in cache
            $today = now()->format('Y-m-d');
            
            // Increment total query count
            $currentCount = Cache::get("queries_count:{$today}", 0);
            Cache::put("queries_count:{$today}", $currentCount + 1, now()->addDay());
            
            // Store query times for average calculation (keep last 1000)
            $queryTimes = Cache::get("query_times:{$today}", []);
            $queryTimes[] = $time;
            
            // Keep only last 1000 query times to avoid memory issues
            if (count($queryTimes) > 1000) {
                $queryTimes = array_slice($queryTimes, -1000);
            }
            
            Cache::put("query_times:{$today}", $queryTimes, now()->addDay());

            // Log slow queries to database
            if ($time >= $threshold) {
                try {
                    // Temporarily disable query listener to prevent recursion
                    DB::connection()->unsetEventDispatcher();
                    
                    SlowQuery::create([
                        'query' => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms' => $time,
                        'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)[4]['file'] ?? null,
                        'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)[4]['line'] ?? null,
                    ]);
                    
                    // Re-enable query listener
                    DB::connection()->setEventDispatcher(app('events'));
                } catch (\Exception $e) {
                    // Prevent monitoring from breaking the app
                    logger()->error('Failed to log slow query: ' . $e->getMessage());
                }
            }
        });
    }
}
