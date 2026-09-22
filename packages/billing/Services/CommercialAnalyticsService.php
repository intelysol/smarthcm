<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Flow\Packages\Billing\Domain\Enums\InvoiceStatus;
use Flow\Packages\Billing\Domain\Enums\SubscriptionStatus;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Illuminate\Support\Facades\DB;

class CommercialAnalyticsService
{
    public function __construct(
        protected PricingEngine $pricingEngine
    ) {}

    /**
     * Get platform commercial executive dashboard metrics.
     */
    public function getExecutiveMetrics(): array
    {
        $activeSubs = BillingSubscription::with('plan.prices')
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->get();

        $mrr = 0.0;
        foreach ($activeSubs as $sub) {
            $calc = $this->pricingEngine->calculatePlanTotal($sub->plan, ['seat' => $sub->quantity]);
            $intervalMonths = $sub->plan->billing_interval->months();
            $monthlyRate = $calc['subtotal'] / max(1, $intervalMonths);
            $mrr += $monthlyRate;
        }
        $mrr = round($mrr, 2);
        $arr = round($mrr * 12, 2);

        $trialCount = BillingSubscription::where('status', SubscriptionStatus::TRIALING->value)->count();
        $pastDueCount = BillingSubscription::where('status', SubscriptionStatus::PAST_DUE->value)->count();
        $suspendedCount = BillingSubscription::where('status', SubscriptionStatus::SUSPENDED->value)->count();

        // Invoices metrics for current month
        $startOfMonth = now()->startOfMonth();
        $totalBilled = (float) BillingInvoice::where('issue_date', '>=', $startOfMonth)->sum('total_amount');
        $totalCollected = (float) BillingInvoice::where('issue_date', '>=', $startOfMonth)->sum('amount_paid');
        $outstandingBalance = (float) BillingInvoice::where('status', '!=', InvoiceStatus::PAID->value)->sum('balance_due');

        $collectionRate = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 100.0;

        $uniqueActiveTenants = $activeSubs->pluck('tenant_id')->unique()->count();
        $arpt = $uniqueActiveTenants > 0 ? round($mrr / $uniqueActiveTenants, 2) : 0.0;

        // Plan distribution
        $planDistribution = BillingSubscription::where('status', SubscriptionStatus::ACTIVE->value)
            ->join('billing_plans', 'billing_subscriptions.plan_id', '=', 'billing_plans.id')
            ->select('billing_plans.name', DB::raw('count(*) as count'))
            ->groupBy('billing_plans.name')
            ->get()
            ->toArray();

        return [
            'mrr' => $mrr,
            'arr' => $arr,
            'active_subscriptions' => $activeSubs->count(),
            'trial_subscriptions' => $trialCount,
            'past_due_subscriptions' => $pastDueCount,
            'suspended_subscriptions' => $suspendedCount,
            'total_billed_mtd' => round($totalBilled, 2),
            'total_collected_mtd' => round($totalCollected, 2),
            'outstanding_balance' => round($outstandingBalance, 2),
            'collection_rate' => $collectionRate,
            'average_revenue_per_tenant' => $arpt,
            'plan_distribution' => $planDistribution,
        ];
    }
}
