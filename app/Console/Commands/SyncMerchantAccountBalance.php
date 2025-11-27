<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncMerchantAccountBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merchant:sync-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync merchant account balance with total subscription billing history revenue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing merchant account balance...');

        // Get total revenue from billing history
        $totalRevenue = DB::table('subscription_billing_history')
            ->where('status', 'paid')
            ->sum('amount');

        $this->info("Total revenue from billing history: ₱" . number_format($totalRevenue, 2));

        // Get merchant account
        $merchantAccount = DB::table('mock_payment_cards')
            ->where('card_id', 'MERCH-PETCONNECT-001')
            ->first();

        if (!$merchantAccount) {
            $this->error('Merchant account not found (card_id: MERCH-PETCONNECT-001)');
            return Command::FAILURE;
        }

        $this->info("Current merchant balance: ₱" . number_format($merchantAccount->balance, 2));

        // Update merchant account balance
        DB::table('mock_payment_cards')
            ->where('card_id', 'MERCH-PETCONNECT-001')
            ->update(['balance' => $totalRevenue]);

        $this->info("Updated merchant balance to: ₱" . number_format($totalRevenue, 2));
        $this->newLine();
        $this->info('✓ Merchant account balance synced successfully!');

        return Command::SUCCESS;
    }
}
