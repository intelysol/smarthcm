<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Flow\Packages\Billing\Domain\Enums\EntitlementType;
use Flow\Packages\Billing\Domain\Enums\SubscriptionStatus;
use Flow\Packages\Billing\Domain\Models\BillingPlanEntitlement;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EntitlementResolver
{
    /**
     * Get active subscription for a tenant.
     */
    public function getActiveSubscription(string $tenantId): ?BillingSubscription
    {
        return BillingSubscription::with(['plan.entitlements'])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [
                SubscriptionStatus::ACTIVE->value,
                SubscriptionStatus::TRIALING->value,
                SubscriptionStatus::PAST_DUE->value,
            ])
            ->latest('starts_at')
            ->first();
    }

    /**
     * Check if a tenant has a specific commercial entitlement enabled.
     */
    public function hasFeature(string $tenantId, string $featureKey): bool
    {
        $entitlements = $this->getAllEntitlements($tenantId);

        if (! isset($entitlements[$featureKey])) {
            return false;
        }

        $ent = $entitlements[$featureKey];

        return (bool) ($ent['is_enabled'] ?? false);
    }

    /**
     * Get numeric limit for an entitlement key.
     */
    public function getLimit(string $tenantId, string $limitKey): ?int
    {
        $entitlements = $this->getAllEntitlements($tenantId);

        if (! isset($entitlements[$limitKey])) {
            return null;
        }

        return isset($entitlements[$limitKey]['limit_value'])
            ? (int) $entitlements[$limitKey]['limit_value']
            : null;
    }

    /**
     * Check if current usage is within the provisioned limit.
     */
    public function checkLimit(string $tenantId, string $limitKey, int $currentUsage = 0): bool
    {
        $limit = $this->getLimit($tenantId, $limitKey);

        // If null or zero, unlimited or not restricted
        if ($limit === null || $limit === 0) {
            return true;
        }

        return $currentUsage < $limit;
    }

    /**
     * Check if tenant is commercially permitted to add an employee.
     */
    public function canAddEmployee(string $tenantId): bool
    {
        $subscription = $this->getActiveSubscription($tenantId);

        if (! $subscription || ! $subscription->isUsable()) {
            return false;
        }

        $limit = $this->getLimit($tenantId, 'employee_limit');

        if ($limit === null || $limit === 0) {
            return true;
        }

        $currentCount = DB::table('employees')
            ->where('tenant_id', $tenantId)
            ->where('employment_status', 'active')
            ->count();

        return $currentCount < $limit;
    }

    /**
     * Get all resolved entitlements for a tenant.
     *
     * @param string $tenantId
     * @return array<string, array{key: string, type: string, is_enabled: bool, limit_value: ?int, metadata: array}>
     */
    public function getAllEntitlements(string $tenantId): array
    {
        $subscription = $this->getActiveSubscription($tenantId);

        if (! $subscription || ! $subscription->isUsable()) {
            return [];
        }

        // Check grace period on past due
        if ($subscription->status === SubscriptionStatus::PAST_DUE) {
            if ($subscription->grace_ends_at && now()->gt($subscription->grace_ends_at)) {
                return []; // Grace expired
            }
        }

        $plan = $subscription->plan;
        if (! $plan) {
            return [];
        }

        $result = [];
        foreach ($plan->entitlements as $ent) {
            $result[$ent->entitlement_key] = [
                'key' => $ent->entitlement_key,
                'type' => $ent->entitlement_type instanceof EntitlementType ? $ent->entitlement_type->value : (string) $ent->entitlement_type,
                'is_enabled' => (bool) $ent->is_enabled,
                'limit_value' => $ent->limit_value !== null ? (int) $ent->limit_value : null,
                'metadata' => $ent->metadata ?? [],
            ];
        }

        return $result;
    }
}
