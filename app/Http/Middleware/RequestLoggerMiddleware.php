<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        
        $today = now()->format('Y-m-d');
        
        // Increment request count for today
        Cache::increment("requests_count:{$today}", 1);
        
        // Track response times (store last 100 response times)
        $responseTimes = Cache::get("response_times:{$today}", []);
        $responseTimes[] = $responseTime;
        
        // Keep only last 100 measurements
        if (count($responseTimes) > 100) {
            array_shift($responseTimes);
        }
        
        Cache::put("response_times:{$today}", $responseTimes, now()->addDay());
        
        // Track errors (4xx and 5xx)
        if ($response->getStatusCode() >= 400) {
            Cache::increment("errors_count:{$today}", 1);
        }
        
        return $response;
    }
}
