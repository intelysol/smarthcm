<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Flow\Packages\Billing\Domain\Enums\SubscriptionStatus;
use Flow\Packages\Billing\Domain\Models\BillingEvent;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionLifecycleService
{
    public function __construct(
        protected ProrationCalculator $prorationCalculator,
        protected PricingEngine $pricingEngine
    ) {}

    /**
     * Start a commercial trial for a tenant.
     */
    public function startTrial(Tenant $tenant, BillingPlan $plan, ?int $trialDays = null): BillingSubscription
    {
        $days = $trialDays ?? $plan->trial_period_days ?: 14;
        $now = Carbon::now();
        $trialEnd = (clone $now)->addDays($days);

        return DB::transaction(function () use ($tenant, $plan, $now, $trialEnd) {
            // Cancel any active subscriptions first
            BillingSubscription::where('tenant_id', $tenant->id)
                ->whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIALING->value])
                ->update(['status' => SubscriptionStatus::CANCELLED->value, 'cancelled_at' => $now]);

            $sub = BillingSubscription::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::TRIALING,
                'quantity' => 1,
                'currency' => $plan->currency,
                'starts_at' => $now,
                'trial_ends_at' => $trialEnd,
                'ends_at' => $trialEnd,
                'billing_anchor_day' => (int) $now->day,
                'current_cycle_start' => $now,
                'current_cycle_end' => $trialEnd,
            ]);

            $this->recordEvent($tenant->id, 'SubscriptionTrialStarted', [
                'subscription_id' => $sub->id,
                'plan_id' => $plan->id,
                'plan_code' => $plan->code,
                'trial_ends_at' => $trialEnd->toIso8601String(),
            ]);

            return $sub;
        });
    }

    /**
     * Activate a paid subscription.
     */
    public function activateSubscription(
        Tenant $tenant,
        BillingPlan $plan,
        int $quantity = 1,
        ?Carbon $startDate = null
    ): BillingSubscription {
        $now = $startDate ?? Carbon::now();
        $intervalMonths = $plan->billing_interval->months();
        $cycleEnd = (clone $now)->addMonths($intervalMonths);

        return DB::transaction(function () use ($tenant, $plan, $quantity, $now, $cycleEnd) {
            BillingSubscription::where('tenant_id', $tenant->id)
                ->whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIALING->value])
                ->update(['status' => SubscriptionStatus::CANCELLED->value, 'cancelled_at' => $now]);

            $sub = BillingSubscription::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::ACTIVE,
                'quantity' => max(1, $quantity),
                'currency' => $plan->currency,
                'starts_at' => $now,
                'billing_anchor_day' => (int) $now->day,
                'current_cycle_start' => $now,
                'current_cycle_end' => $cycleEnd,
                'ends_at' => $cycleEnd,
                'auto_renew' => true,
            ]);

            $this->recordEvent($tenant->id, 'SubscriptionActivated', [
                'subscription_id' => $sub->id,
                'plan_id' => $plan->id,
                'plan_code' => $plan->code,
                'quantity' => $quantity,
                'cycle_end' => $cycleEnd->toIso8601String(),
            ]);

            return $sub;
        });
    }

    /**
     * Renew an active subscription for the next billing cycle.
     */
    public function renew(BillingSubscription $subscription): BillingSubscription
    {
        $now = Carbon::now();
        $intervalMonths = $subscription->plan->billing_interval->months();

        $newCycleStart = $subscription->current_cycle_end ?? $now;
        $newCycleEnd = (clone $newCycleStart)->addMonths($intervalMonths);

        $subscription->update([
            'status' => SubscriptionStatus::ACTIVE,
            'current_cycle_start' => $newCycleStart,
            'current_cycle_end' => $newCycleEnd,
            'ends_at' => $newCycleEnd,
            'grace_ends_at' => null,
        ]);

        $this->recordEvent($subscription->tenant_id, 'SubscriptionRenewed', [
            'subscription_id' => $subscription->id,
            'cycle_start' => $newCycleStart->toIso8601String(),
            'cycle_end' => $newCycleEnd->toIso8601String(),
        ]);

        return $subscription;
    }

    /**
     * Upgrade or change plan with deterministic proration.
     */
    public function changePlan(
        BillingSubscription $subscription,
        BillingPlan $newPlan,
        int $newQuantity = 1
    ): array {
        $now = Carbon::now();
        $cycleStart = $subscription->current_cycle_start ?? (clone $now)->startOfMonth();
        $cycleEnd = $subscription->current_cycle_end ?? (clone $now)->endOfMonth();

        // Calculate current recurring rate vs new plan recurring rate
        $currentCalc = $this->pricingEngine->calculatePlanTotal($subscription->plan, ['seat' => $subscription->quantity]);
        $newCalc = $this->pricingEngine->calculatePlanTotal($newPlan, ['seat' => $newQuantity]);

        $proration = $this->prorationCalculator->calculate(
            $currentCalc['subtotal'],
            $newCalc['subtotal'],
            $cycleStart,
            $cycleEnd,
            $now
        );

        $oldPlanCode = $subscription->plan->code;

        $subscription->update([
            'plan_id' => $newPlan->id,
            'quantity' => $newQuantity,
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        $eventType = $newCalc['subtotal'] >= $currentCalc['subtotal']
            ? 'SubscriptionUpgraded'
            : 'SubscriptionDowngraded';

        $this->recordEvent($subscription->tenant_id, $eventType, [
            'subscription_id' => $subscription->id,
            'from_plan' => $oldPlanCode,
            'to_plan' => $newPlan->code,
            'old_quantity' => $subscription->quantity,
            'new_quantity' => $newQuantity,
            'proration' => $proration,
        ]);

        return [
            'subscription' => $subscription,
            'proration' => $proration,
            'new_recurring_total' => $newCalc['subtotal'],
            'immediate_charge' => $proration['immediate_charge'],
            'credit_issued' => $proration['credit_issued'],
        ];
    }

    /**
     * Mark subscription past due and establish grace period.
     */
    public function markPastDue(BillingSubscription $subscription, int $graceDays = 7): BillingSubscription
    {
        $now = Carbon::now();
        $graceEnd = (clone $now)->addDays($graceDays);

        $subscription->update([
            'status' => SubscriptionStatus::PAST_DUE,
            'grace_ends_at' => $graceEnd,
        ]);

        $this->recordEvent($subscription->tenant_id, 'SubscriptionPastDue', [
            'subscription_id' => $subscription->id,
            'grace_ends_at' => $graceEnd->toIso8601String(),
        ]);

        return $subscription;
    }

    /**
     * Suspend subscription (preserves data, restricts operations).
     */
    public function suspend(BillingSubscription $subscription, string $reason = 'Payment overdue'): BillingSubscription
    {
        $now = Carbon::now();

        $subscription->update([
            'status' => SubscriptionStatus::SUSPENDED,
            'suspended_at' => $now,
            'cancellation_reason' => $reason,
        ]);

        $this->recordEvent($subscription->tenant_id, 'SubscriptionSuspended', [
            'subscription_id' => $subscription->id,
            'reason' => $reason,
            'suspended_at' => $now->toIso8601String(),
        ]);

        return $subscription;
    }

    /**
     * Reactivate a suspended or paused subscription.
     */
    public function reactivate(BillingSubscription $subscription): BillingSubscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::ACTIVE,
            'suspended_at' => null,
            'paused_at' => null,
            'grace_ends_at' => null,
        ]);

        $this->recordEvent($subscription->tenant_id, 'SubscriptionReactivated', [
            'subscription_id' => $subscription->id,
        ]);

        return $subscription;
    }

    /**
     * Cancel subscription.
     */
    public function cancel(BillingSubscription $subscription, string $reason = 'Customer requested'): BillingSubscription
    {
        $now = Carbon::now();

        $subscription->update([
            'status' => SubscriptionStatus::CANCELLED,
            'cancelled_at' => $now,
            'cancellation_reason' => $reason,
            'auto_renew' => false,
        ]);

        $this->recordEvent($subscription->tenant_id, 'SubscriptionCancelled', [
            'subscription_id' => $subscription->id,
            'reason' => $reason,
            'cancelled_at' => $now->toIso8601String(),
        ]);

        return $subscription;
    }

    /**
     * Record an immutable commercial audit event.
     */
    protected function recordEvent(string $tenantId, string $eventType, array $payload): void
    {
        BillingEvent::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'event_type' => $eventType,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
