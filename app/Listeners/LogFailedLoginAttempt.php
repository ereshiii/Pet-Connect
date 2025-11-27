<?php

namespace App\Listeners;

use App\Models\FailedLoginAttempt;
use App\Models\BlockedIp;
use App\Models\SecurityEvent;
use Laravel\Fortify\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogFailedLoginAttempt
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $request = request();
        $ipAddress = $request->ip();
        $email = $event->user?->email ?? $request->input('email') ?? 'unknown';
        
        // Log the failed attempt
        FailedLoginAttempt::create([
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'reason' => 'Invalid credentials',
        ]);
        
        // Check if IP should be blocked
        $recentAttempts = FailedLoginAttempt::where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();
        
        if ($recentAttempts >= config('monitoring.failed_login_threshold', 5)) {
            $existingBlock = BlockedIp::where('ip_address', $ipAddress)->first();
            
            if (!$existingBlock) {
                BlockedIp::create([
                    'ip_address' => $ipAddress,
                    'reason' => "Multiple failed login attempts ({$recentAttempts} in 15 minutes)",
                    'attempts_count' => $recentAttempts,
                    'blocked_at' => now(),
                    'expires_at' => now()->addHours(config('monitoring.block_duration_hours', 24)),
                ]);
                
                // Log security event
                SecurityEvent::create([
                    'type' => 'Authentication',
                    'description' => "IP address {$ipAddress} blocked after {$recentAttempts} failed login attempts",
                    'severity' => 'high',
                    'ip_address' => $ipAddress,
                    'metadata' => ['attempts' => $recentAttempts, 'email' => $email],
                ]);
            }
        }
    }
}
