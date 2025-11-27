<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\SubscriptionBillingService;

class PopulateSubscriptionBillingHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:populate-billing-history {--subscription-id= : Specific subscription ID to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate billing history records for all existing subscriptions';

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
        $this->info('Starting subscription billing history population...');

        $subscriptionId = $this->option('subscription-id');

        if ($subscriptionId) {
            // Process specific subscription
            $this->processSubscription($subscriptionId);
        } else {
            // Process all subscriptions
            $subscriptions = DB::table('subscriptions')->get();
            
            $this->info("Found {$subscriptions->count()} subscriptions to process");
            
            $progressBar = $this->output->createProgressBar($subscriptions->count());
            $progressBar->start();

            $totalRecords = 0;

            foreach ($subscriptions as $subscription) {
                $records = $this->billingService->generateBillingHistory($subscription->id);
                $totalRecords += $records;
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);
            
            $this->info("Successfully created {$totalRecords} billing history records!");
            
            // Show summary
            $totalRevenue = $this->billingService->getTotalRevenue();
            $this->info("Total revenue in billing history: ₱" . number_format($totalRevenue, 2));
            
            $revenueByPlan = $this->billingService->getRevenueByPlan();
            $this->newLine();
            $this->info("Revenue by plan:");
            foreach ($revenueByPlan as $slug => $data) {
                $this->line("  - {$data['name']}: ₱" . number_format($data['total'], 2));
            }
        }

        return Command::SUCCESS;
    }

    protected function processSubscription($subscriptionId)
    {
        $this->info("Processing subscription ID: {$subscriptionId}");
        
        $records = $this->billingService->generateBillingHistory($subscriptionId);
        
        if ($records > 0) {
            $this->info("Created {$records} billing history records for subscription {$subscriptionId}");
        } else {
            $this->warn("No new records created for subscription {$subscriptionId}");
        }
    }
}
