<?php

namespace App\Console\Commands;

use App\Models\FailedLoginAttempt;
use App\Models\SecurityEvent;
use App\Models\SlowQuery;
use Illuminate\Console\Command;

class CleanupOldMonitoringLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old monitoring logs based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retentionDays = config('monitoring.retention_days', [
            'failed_logins' => 30,
            'security_events' => 90,
            'slow_queries' => 7,
        ]);

        if (!$this->option('force')) {
            $this->info('Cleaning up monitoring logs based on retention policy:');
            $this->line("  Failed Logins: {$retentionDays['failed_logins']} days");
            $this->line("  Security Events: {$retentionDays['security_events']} days");
            $this->line("  Slow Queries: {$retentionDays['slow_queries']} days");
            $this->newLine();
            
            if (!$this->confirm('Do you want to continue?', true)) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        // Clean up failed login attempts (in chunks to avoid memory issues)
        $failedLoginDate = now()->subDays($retentionDays['failed_logins']);
        $deletedFailedLogins = 0;
        do {
            $deleted = FailedLoginAttempt::where('created_at', '<', $failedLoginDate)
                ->limit(1000)
                ->delete();
            $deletedFailedLogins += $deleted;
        } while ($deleted > 0);
        $this->info("Deleted {$deletedFailedLogins} old failed login attempts.");

        // Clean up security events (in chunks)
        $securityEventDate = now()->subDays($retentionDays['security_events']);
        $deletedSecurityEvents = 0;
        do {
            $deleted = SecurityEvent::where('created_at', '<', $securityEventDate)
                ->limit(1000)
                ->delete();
            $deletedSecurityEvents += $deleted;
        } while ($deleted > 0);
        $this->info("Deleted {$deletedSecurityEvents} old security events.");

        // Clean up slow queries (in chunks)
        $slowQueryDate = now()->subDays($retentionDays['slow_queries']);
        $deletedSlowQueries = 0;
        do {
            $deleted = SlowQuery::where('created_at', '<', $slowQueryDate)
                ->limit(1000)
                ->delete();
            $deletedSlowQueries += $deleted;
        } while ($deleted > 0);
        $this->info("Deleted {$deletedSlowQueries} old slow query logs.");

        $this->newLine();
        $this->info('Cleanup completed successfully!');
        
        return 0;
    }
}
