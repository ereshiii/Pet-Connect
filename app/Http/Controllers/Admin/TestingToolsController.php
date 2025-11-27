<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TestingToolsController extends Controller
{
    public function mockPayment(): Response
    {
        // Get test cards from database or use defaults
        $testCards = DB::table('mock_payment_cards')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->card_id,
                    'card_number' => $card->card_number,
                    'card_holder' => $card->card_holder,
                    'expiry' => $card->expiry,
                    'cvv' => $card->cvv,
                    'balance' => (float) $card->balance,
                    'bank' => $card->bank,
                ];
            })
            ->toArray();
        
        // If no cards exist, create defaults
        if (empty($testCards)) {
            $defaultCards = [
                [
                    'card_id' => 'card_test_001',
                    'card_number' => '4532 1234 5678 9010',
                    'card_holder' => 'John Test Doe',
                    'expiry' => '12/25',
                    'cvv' => '123',
                    'balance' => 50000.00,
                    'bank' => 'PetConnect Test Bank',
                ],
                [
                    'card_id' => 'card_test_002',
                    'card_number' => '5555 4444 3333 2222',
                    'card_holder' => 'Jane Test Smith',
                    'expiry' => '03/26',
                    'cvv' => '456',
                    'balance' => 25000.00,
                    'bank' => 'Test Bank Inc',
                ],
            ];
            
            foreach ($defaultCards as $card) {
                DB::table('mock_payment_cards')->insert(array_merge($card, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            
            $testCards = array_map(function ($card) {
                return [
                    'id' => $card['card_id'],
                    'card_number' => $card['card_number'],
                    'card_holder' => $card['card_holder'],
                    'expiry' => $card['expiry'],
                    'cvv' => $card['cvv'],
                    'balance' => (float) $card['balance'],
                    'bank' => $card['bank'],
                ];
            }, $defaultCards);
        }

        // Merchant Account from database
        $merchantCard = DB::table('mock_payment_cards')
            ->where('card_id', 'MERCH-PETCONNECT-001')
            ->first();
        
        // Get transaction count from billing history
        $transactionCount = DB::table('subscription_billing_history')
            ->where('status', 'paid')
            ->count();
        
        $merchantAccount = [
            'account_number' => 'MERCH-PETCONNECT-001',
            'account_name' => 'PetConnect Merchant Account',
            'total_received' => $merchantCard ? (float) $merchantCard->balance : 0,
            'transaction_count' => $transactionCount,
        ];

        return Inertia::render('1adminPages/TestingTools/MockPayment', [
            'test_cards' => $testCards,
            'merchant_account' => $merchantAccount,
        ]);
    }

    public function storeCard(Request $request)
    {
        $validated = $request->validate([
            'card_number' => 'required|string|max:19',
            'card_holder' => 'required|string|max:255',
            'expiry' => 'required|string|max:5',
            'cvv' => 'required|string|max:4',
            'balance' => 'required|numeric|min:0',
            'bank' => 'required|string|max:255',
        ]);

        $cardId = 'card_' . substr(md5(uniqid()), 0, 8);

        DB::table('mock_payment_cards')->insert([
            'card_id' => $cardId,
            'card_number' => $validated['card_number'],
            'card_holder' => $validated['card_holder'],
            'expiry' => $validated['expiry'],
            'cvv' => $validated['cvv'],
            'balance' => $validated['balance'],
            'bank' => $validated['bank'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Test card added successfully');
    }

    public function updateCard(Request $request, string $cardId)
    {
        $validated = $request->validate([
            'card_number' => 'required|string|max:19',
            'card_holder' => 'required|string|max:255',
            'expiry' => 'required|string|max:5',
            'cvv' => 'required|string|max:4',
            'balance' => 'required|numeric|min:0',
            'bank' => 'required|string|max:255',
        ]);

        DB::table('mock_payment_cards')
            ->where('card_id', $cardId)
            ->update([
                'card_number' => $validated['card_number'],
                'card_holder' => $validated['card_holder'],
                'expiry' => $validated['expiry'],
                'cvv' => $validated['cvv'],
                'balance' => $validated['balance'],
                'bank' => $validated['bank'],
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Test card updated successfully');
    }

    public function deleteCard(string $cardId)
    {
        DB::table('mock_payment_cards')
            ->where('card_id', $cardId)
            ->delete();

        return redirect()->back()->with('success', 'Test card deleted successfully');
    }

    public function subscriptionRemoval(): Response
    {
        // Get all active subscriptions
        $activeSubscriptions = DB::table('subscriptions')
            ->join('users', 'subscriptions.user_id', '=', 'users.id')
            ->leftJoin('clinic_registrations', 'users.id', '=', 'clinic_registrations.user_id')
            ->where(function($query) {
                $query->whereNull('subscriptions.ends_at')
                      ->orWhere('subscriptions.ends_at', '>', now());
            })
            ->select([
                'subscriptions.id',
                'subscriptions.user_id',
                'subscriptions.type',
                'subscriptions.stripe_status',
                'subscriptions.created_at',
                'clinic_registrations.clinic_name',
                'users.email',
            ])
            ->orderBy('subscriptions.created_at', 'desc')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'clinic_name' => $sub->clinic_name ?? 'Unknown',
                    'email' => $sub->email,
                    'plan' => $sub->type,
                    'status' => $sub->stripe_status,
                    'started_at' => date('M d, Y', strtotime($sub->created_at)),
                ];
            });

        return Inertia::render('1adminPages/TestingTools/SubscriptionRemoval', [
            'subscriptions' => $activeSubscriptions,
        ]);
    }

    public function removeSubscription(Request $request, $id)
    {
        $subscription = DB::table('subscriptions')->where('id', $id)->first();

        if (!$subscription) {
            return redirect()->back()->with('error', 'Subscription not found');
        }

        // Mark subscription as ended
        DB::table('subscriptions')
            ->where('id', $id)
            ->update([
                'ends_at' => now(),
                'stripe_status' => 'canceled',
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Subscription removed successfully');
    }

    public function accountReset(): Response
    {
        // Get all users with stats
        $users = User::with(['profile', 'clinicRegistration'])
            ->where('is_admin', false)
            ->get()
            ->map(function ($user) {
                $appointmentsCount = DB::table('appointments')->where('user_id', $user->id)->count();
                $petsCount = DB::table('pets')->where('user_id', $user->id)->count();
                $subscriptionsCount = DB::table('subscriptions')->where('user_id', $user->id)->count();

                return [
                    'id' => $user->id,
                    'name' => $user->profile ? $user->profile->first_name . ' ' . $user->profile->last_name : $user->email,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                    'clinic_name' => $user->clinicRegistration->clinic_name ?? null,
                    'created_at' => $user->created_at->format('M d, Y'),
                    'stats' => [
                        'appointments' => $appointmentsCount,
                        'pets' => $petsCount,
                        'subscriptions' => $subscriptionsCount,
                    ],
                ];
            });

        return Inertia::render('1adminPages/TestingTools/AccountReset', [
            'users' => $users,
        ]);
    }

    public function resetAccount(Request $request, $id)
    {
        $request->validate([
            'reset_type' => 'required|in:soft,hard',
        ]);

        $resetType = $request->input('reset_type');
        $user = User::findOrFail($id);

        if ($resetType === 'soft') {
            // Soft reset: Clear account data but keep the account
            DB::table('appointments')->where('user_id', $id)->delete();
            DB::table('pets')->where('user_id', $id)->delete();
            DB::table('subscriptions')->where('user_id', $id)->update([
                'ends_at' => now(),
                'stripe_status' => 'canceled',
            ]);
            DB::table('clinic_reviews')->where('user_id', $id)->delete();
            DB::table('user_clinic_favorites')->where('user_id', $id)->delete();
            DB::table('patient_edit_logs')->where('user_id', $id)->delete();
            DB::table('payment_methods')->where('user_id', $id)->delete();

            $message = 'Account data reset successfully (soft reset)';
        } else {
            // Hard reset: Delete entire account
            // Delete in order to respect foreign key constraints
            
            // First, get clinic_id if user has a clinic registration
            $clinicRegistration = DB::table('clinic_registrations')->where('user_id', $id)->first();
            
            if ($clinicRegistration) {
                // Delete clinic-related data first
                DB::table('clinic_services')->where('clinic_id', $clinicRegistration->id)->delete();
                DB::table('clinic_operating_hours')->where('clinic_id', $clinicRegistration->id)->delete();
                DB::table('clinic_staff')->where('clinic_id', $clinicRegistration->id)->delete();
                DB::table('clinics')->where('registration_id', $clinicRegistration->id)->delete();
            }
            
            // Delete user-related data
            DB::table('clinic_reviews')->where('user_id', $id)->delete();
            DB::table('user_clinic_favorites')->where('user_id', $id)->delete();
            DB::table('patient_edit_logs')->where('user_id', $id)->delete();
            DB::table('device_tokens')->where('user_id', $id)->delete();
            DB::table('push_notifications')->where('user_id', $id)->delete();
            DB::table('payment_methods')->where('user_id', $id)->delete();
            DB::table('appointments')->where('user_id', $id)->delete();
            DB::table('pets')->where('user_id', $id)->delete();
            DB::table('subscriptions')->where('user_id', $id)->delete();
            DB::table('clinic_registrations')->where('user_id', $id)->delete();
            DB::table('user_profiles')->where('user_id', $id)->delete();
            // Remove any remaining records that reference this user_id to avoid FK constraint errors
            DB::table('clinic_staff')->where('user_id', $id)->delete();
            DB::table('invoices')->where('user_id', $id)->delete();
            DB::table('payments')->where('user_id', $id)->delete();
            DB::table('clinic_equipment')->where('user_id', $id)->delete();

            // Attempt a dynamic cleanup: for sqlite, scan all tables' foreign keys and delete rows
            try {
                $pdo = DB::getPdo();
                $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

                if ($driver === 'sqlite') {
                    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                    foreach ($tables as $t) {
                        $tableName = $t->name;
                        $fks = DB::select("PRAGMA foreign_key_list('" . $tableName . "')");
                        foreach ($fks as $fk) {
                            // pragma returns 'table' as referenced table name and 'from' as local column
                            if (isset($fk->table) && $fk->table === 'users' && isset($fk->from)) {
                                $localCol = $fk->from;
                                try {
                                    DB::table($tableName)->where($localCol, $id)->delete();
                                    Log::info("Deleted referencing rows in {$tableName}.{$localCol} for user_id {$id}");
                                } catch (\Exception $inner) {
                                    // continue on error
                                    Log::error('Could not delete from ' . $tableName . ': ' . $inner->getMessage());
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Log but don't block deletion attempt
                Log::error('Dynamic FK cleanup failed: ' . $e->getMessage());
            }

            // Final attempt to delete the user
            try {
                $user->delete();
            } catch (\Exception $e) {
                // If deletion still fails, log and rethrow so the exception surfaces in logs
                Log::error('Failed to delete user after cleaning related records: ' . $e->getMessage());
                throw $e;
            }

            $message = 'Account deleted successfully (hard reset)';
        }

        return redirect()->back()->with('success', $message);
    }
}
