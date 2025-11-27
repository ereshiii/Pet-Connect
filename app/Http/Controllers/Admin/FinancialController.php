<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicRegistration;
use App\Models\User;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FinancialController extends Controller
{
    protected SubscriptionBillingService $billingService;

    public function __construct(SubscriptionBillingService $billingService)
    {
        $this->billingService = $billingService;
    }
    public function subscriptions(Request $request): Response
    {
        $period = $request->get('period', 'monthly');
        
        // Get subscriptions with calculated billing information
        $subscriptions = DB::table('subscriptions')
            ->join('users', 'subscriptions.user_id', '=', 'users.id')
            ->leftJoin('clinic_registrations', 'users.id', '=', 'clinic_registrations.user_id')
            ->leftJoin('subscription_plans', 'subscriptions.stripe_price', '=', 'subscription_plans.slug')
            ->select([
                'subscriptions.id',
                'subscriptions.user_id',
                'subscriptions.type',
                'subscriptions.stripe_price',
                'subscriptions.ends_at',
                'subscriptions.created_at',
                'clinic_registrations.clinic_name',
                'subscription_plans.name as plan_name',
                'subscription_plans.price as plan_price',
                'subscription_plans.slug as plan_slug',
            ])
            ->orderBy('subscriptions.created_at', 'desc')
            ->get()
            ->map(function ($sub) {
                $startDate = \Carbon\Carbon::parse($sub->created_at);
                $endDate = $sub->ends_at ? \Carbon\Carbon::parse($sub->ends_at) : now();
                $isActive = !$sub->ends_at || $endDate->isFuture();
                
                // Calculate months subscribed
                $monthsSubscribed = $isActive ? $startDate->diffInMonths(now()) + 1 : $startDate->diffInMonths($endDate);
                
                // Calculate total revenue from this subscription
                $pricePerMonth = (float) ($sub->plan_price ?? 0);
                $totalRevenue = $monthsSubscribed * $pricePerMonth;
                
                // Calculate next billing date (start date + months)
                $nextBillingDate = $isActive ? $startDate->copy()->addMonths($monthsSubscribed) : null;
                
                // Get billing history count
                $billingCount = DB::table('subscription_billing_history')
                    ->where('subscription_id', $sub->id)
                    ->count();
                
                return [
                    'id' => $sub->id,
                    'clinic_name' => $sub->clinic_name ?? 'Unknown Clinic',
                    'plan' => $sub->plan_slug ?? 'basic-clinic',
                    'amount' => $pricePerMonth,
                    'total_revenue' => $totalRevenue,
                    'months_subscribed' => $monthsSubscribed,
                    'billing_cycle' => $monthsSubscribed . ($monthsSubscribed === 1 ? ' month' : ' months'),
                    'next_billing_date' => $isActive && $nextBillingDate ? $nextBillingDate->format('M d, Y') : 'Ended',
                    'started_at' => $startDate->format('M d, Y'),
                    'is_active' => $isActive,
                    'billing_history_count' => $billingCount,
                ];
            });

        $revenueStats = [
            'total_revenue' => $this->billingService->getTotalRevenue(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'active_subscriptions' => $this->countActiveSubscriptions(),
            'growth_percentage' => $this->calculateGrowthPercentage(),
            'mrr' => $this->calculateMRR(),
            'arr' => $this->calculateARR(),
        ];

        $revenueHistory = $this->getRevenueHistory($period);
        $planDistribution = $this->getPlanDistribution();
        $churnRate = $this->calculateChurnRate();

        return Inertia::render('1adminPages/Financial/Subscriptions', [
            'subscriptions' => [
                'data' => $subscriptions,
                'total' => $subscriptions->count(),
            ],
            'revenue_stats' => $revenueStats,
            'revenue_history' => $revenueHistory,
            'plan_distribution' => $planDistribution,
            'churn_rate' => $churnRate,
            'current_period' => $period,
        ]);
    }

    private function calculateMonthlyRevenue(): float
    {
        return $this->calculateMRR();
    }

    private function calculateMRR(): float
    {
        // MRR = Monthly Recurring Revenue from currently active subscriptions
        $mrr = DB::table('subscriptions')
            ->leftJoin('subscription_plans', 'subscriptions.stripe_price', '=', 'subscription_plans.slug')
            ->where(function($query) {
                $query->whereNull('subscriptions.ends_at')
                      ->orWhere('subscriptions.ends_at', '>', now());
            })
            ->sum('subscription_plans.price');
        
        return (float) $mrr;
    }

    private function calculateARR(): float
    {
        return $this->calculateMRR() * 12;
    }

    private function countActiveSubscriptions(): int
    {
        return DB::table('subscriptions')
            ->where(function($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->count();
    }

    private function calculateGrowthPercentage(): float
    {
        $currentMonth = DB::table('subscriptions')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonth = DB::table('subscriptions')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        if ($lastMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function getRevenueHistory(string $period = 'monthly'): array
    {
        $labels = [];
        $data = [];

        [$intervals, $format] = $this->getPeriodIntervals($period);

        for ($i = $intervals - 1; $i >= 0; $i--) {
            $date = $this->getDateForInterval($period, $i);
            $labels[] = $date->format($format);
            
            [$startDate, $endDate] = $this->getDateRange($period, $date);
            
            // Use billing history for revenue data
            $revenue = DB::table('subscription_billing_history')
                ->where('status', 'paid')
                ->whereBetween('billing_date', [$startDate, $endDate])
                ->sum('amount');
            
            $data[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getPlanDistribution(): array
    {
        $subscriptions = DB::table('subscriptions')
            ->where(function($query) {
                $query->whereNull('subscriptions.ends_at')
                      ->orWhere('subscriptions.ends_at', '>', now());
            })
            ->select('stripe_price')
            ->get();

        $basicClinic = 0;
        $professional = 0;
        $proPlus = 0;

        foreach ($subscriptions as $sub) {
            if ($sub->stripe_price === 'basic-clinic') {
                $basicClinic++;
            } elseif ($sub->stripe_price === 'professional') {
                $professional++;
            } elseif ($sub->stripe_price === 'pro-plus') {
                $proPlus++;
            }
        }

        return [
            'basic' => $basicClinic,
            'professional' => $professional,
            'pro_plus' => $proPlus,
        ];
    }

    private function calculateChurnRate(): float
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $activeAtStart = DB::table('subscriptions')
            ->where('created_at', '<', $startOfMonth)
            ->where(function($query) use ($startOfMonth) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', $startOfMonth);
            })
            ->count();

        $churned = DB::table('subscriptions')
            ->whereBetween('ends_at', [$startOfMonth, $endOfMonth])
            ->count();

        if ($activeAtStart == 0) {
            return 0;
        }

        return round(($churned / $activeAtStart) * 100, 1);
    }

    /**
     * Get intervals and format for period.
     */
    private function getPeriodIntervals(string $period): array
    {
        return match($period) {
            'daily' => [24, 'H:i'],
            'weekly' => [7, 'M d'],
            'monthly' => [30, 'M d'],
            'quarterly' => [4, 'M Y'],
            'semi_annually' => [6, 'M Y'],
            'yearly' => [12, 'M'],
            default => [30, 'M d'],
        };
    }

    /**
     * Get date for interval based on period.
     */
    private function getDateForInterval(string $period, int $i)
    {
        return match($period) {
            'daily' => now()->subHours($i),
            'weekly' => now()->subDays($i),
            'monthly' => now()->subDays($i),
            'quarterly' => now()->subMonths($i),
            'semi_annually' => now()->subMonths($i),
            'yearly' => now()->subMonths($i),
            default => now()->subDays($i),
        };
    }

    /**
     * Get date range for period.
     */
    private function getDateRange(string $period, $date): array
    {
        return match($period) {
            'daily' => [$date->copy()->startOfHour(), $date->copy()->endOfHour()],
            'weekly' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'monthly' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'quarterly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'semi_annually' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'yearly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            default => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
        };
    }
}
