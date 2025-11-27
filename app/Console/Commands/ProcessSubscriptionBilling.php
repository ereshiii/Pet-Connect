<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\SubscriptionBillingService;
use Carbon\Carbon;

class ProcessSubscriptionBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:process-billing {--dry-run : Preview what would be billed without processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process monthly billing for active subscriptions';

    protected SubscriptionBillingService $billingService;

    public function __construct(SubscriptionBillingService $billingService)
    {
        parent::__construct();
        $this->billingService = $billingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No actual billing will be processed');
        }

        $this->info('Processing subscription billing...');

        // Get active subscriptions that need billing
        $subscriptions = DB::table('subscriptions')
            ->leftJoin('subscription_plans', 'subscriptions.stripe_price', '=', 'subscription_plans.slug')
            ->where(function($query) {
                $query->whereNull('subscriptions.ends_at')
                      ->orWhere('subscriptions.ends_at', '>', now());
            })
            ->select(
                'subscriptions.*',
                'subscription_plans.price',
                'subscription_plans.name as plan_name'
            )
            ->get();

        $this->info("Found {$subscriptions->count()} active subscriptions");

        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        foreach ($subscriptions as $subscription) {
            $startDate = Carbon::parse($subscription->created_at);
            $monthsSinceStart = $startDate->diffInMonths(now());
            
            // Calculate expected next billing date
            $expectedBillingDate = $startDate->copy()->addMonths($monthsSinceStart);
            
            // Check if billing is due (within 1 day of expected date)
            $isDue = now()->diffInDays($expectedBillingDate, false) <= 1 && 
                     now()->diffInDays($expectedBillingDate, false) >= 0;

            // Check if already billed this month
            $lastBilling = DB::table('subscription_billing_history')
                ->where('subscription_id', $subscription->id)
                ->where('status', 'paid')
                ->orderBy('billing_date', 'desc')
                ->first();

            $alreadyBilledThisMonth = $lastBilling && 
                Carbon::parse($lastBilling->billing_date)->isSameMonth(now());

            if ($alreadyBilledThisMonth) {
                $skippedCount++;
                continue;
            }

            if (!$isDue) {
                $skippedCount++;
                continue;
            }

            $this->line("Processing: {$subscription->plan_name} for User #{$subscription->user_id} - ₱{$subscription->price}");

            if (!$isDryRun) {
                $success = $this->billingService->processBilling($subscription->id);
                
                if ($success) {
                    $this->info("  ✓ Successfully billed subscription #{$subscription->id}");
                    $successCount++;
                } else {
                    $this->error("  ✗ Failed to bill subscription #{$subscription->id}");
                    $failedCount++;
                }
            } else {
                $this->info("  [DRY RUN] Would bill subscription #{$subscription->id}");
                $successCount++;
            }
        }

        $this->newLine();
        $this->info("Billing Summary:");
        $this->line("  Processed: {$successCount}");
        $this->line("  Failed: {$failedCount}");
        $this->line("  Skipped: {$skippedCount}");

        if (!$isDryRun && $successCount > 0) {
            $totalRevenue = $this->billingService->getTotalRevenue();
            $this->newLine();
            $this->info("Updated total revenue: ₱" . number_format($totalRevenue, 2));
        }

        return Command::SUCCESS;
    }
}
