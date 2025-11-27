<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Failed Login Threshold
    |--------------------------------------------------------------------------
    |
    | Number of failed login attempts within 15 minutes before blocking IP
    |
    */
    'failed_login_threshold' => env('MONITOR_FAILED_LOGIN_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Block Duration (Hours)
    |--------------------------------------------------------------------------
    |
    | How long to block an IP address after exceeding threshold
    |
    */
    'block_duration_hours' => env('MONITOR_BLOCK_DURATION_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold (milliseconds)
    |--------------------------------------------------------------------------
    |
    | Log queries that exceed this execution time
    |
    */
    'slow_query_threshold' => env('MONITOR_SLOW_QUERY_THRESHOLD', 100),

    /*
    |--------------------------------------------------------------------------
    | Enable Query Logging
    |--------------------------------------------------------------------------
    |
    | Enable database query monitoring (can impact performance)
    |
    */
    'enable_query_logging' => env('MONITOR_QUERIES', false),

    /*
    |--------------------------------------------------------------------------
    | Data Retention (Days)
    |--------------------------------------------------------------------------
    |
    | How long to keep monitoring data before cleanup
    |
    */
    'retention_days' => [
        'failed_logins' => 30,
        'security_events' => 90,
        'slow_queries' => 7,
    ],

];
