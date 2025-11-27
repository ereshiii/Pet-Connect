<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockIpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();
        
        $blockedIp = BlockedIp::where('ip_address', $ipAddress)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
        
        if ($blockedIp) {
            // Check if block has expired
            if ($blockedIp->isExpired()) {
                $blockedIp->delete();
                return $next($request);
            }
            
            abort(403, 'Your IP address has been blocked due to suspicious activity.');
        }
        
        return $next($request);
    }
}
