<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Carbon\Carbon;
use Flow\Packages\Billing\Domain\Models\BillingUsageEvent;
use Flow\Packages\Billing\Domain\Models\BillingUsageSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsageMeteringService
{
    public function __construct(
        protected EntitlementResolver $entitlementResolver
    ) {}

    /**
     * Record a tenant usage event idempotently.
     * Returns true if newly recorded, false if duplicate ignored.
     */
    public function recordUsage(
        string $tenantId,
        string $meterKey,
        float $quantity,
        string $idempotencyKey,
        string $source = 'system',
        array $metadata = []
    ): bool {
        // Idempotency check
        $exists = BillingUsageEvent::where('idempotency_key', $idempotencyKey)->exists();
        if ($exists) {
            return false;
        }

        try {
            BillingUsageEvent::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'meter_key' => $meterKey,
                'quantity' => $quantity,
                'recorded_at' => Carbon::now(),
                'idempotency_key' => $idempotencyKey,
                'source' => $source,
                'metadata' => $metadata,
                'created_at' => Carbon::now(),
            ]);

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            // Safe fallback if unique constraint race condition occurred
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Generate or update a pre-aggregated usage snapshot for a tenant and meter.
     */
    public function snapshotPeriod(
        string $tenantId,
        string $meterKey,
        Carbon $periodStart,
        Carbon $periodEnd
    ): BillingUsageSnapshot {
        $total = (float) BillingUsageEvent::where('tenant_id', $tenantId)
            ->where('meter_key', $meterKey)
            ->whereBetween('recorded_at', [$periodStart, $periodEnd])
            ->sum('quantity');

        return BillingUsageSnapshot::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'meter_key' => $meterKey,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            [
                'id' => (string) Str::uuid(),
                'total_quantity' => $total,
                'billable_quantity' => $total,
                'snapshotted_at' => Carbon::now(),
            ]
        );
    }

    /**
     * Get current aggregated consumption for a meter in the current month.
     */
    public function getCurrentUsage(string $tenantId, string $meterKey): float
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Special dynamic meters: active_employees
        if ($meterKey === 'active_employees') {
            return (float) DB::table('employees')
                ->where('tenant_id', $tenantId)
                ->where('employment_status', 'active')
                ->count();
        }

        // Dynamic meter: user_seats
        if ($meterKey === 'user_seats') {
            return (float) DB::table('tenant_user')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count();
        }

        // Check recent snapshot
        $snapshot = BillingUsageSnapshot::where('tenant_id', $tenantId)
            ->where('meter_key', $meterKey)
            ->where('period_start', '>=', $startOfMonth)
            ->first();

        if ($snapshot) {
            // Add any events recorded after snapshot
            $additional = (float) BillingUsageEvent::where('tenant_id', $tenantId)
                ->where('meter_key', $meterKey)
                ->where('recorded_at', '>', $snapshot->snapshotted_at)
                ->where('recorded_at', '<=', $endOfMonth)
                ->sum('quantity');

            return (float) $snapshot->total_quantity + $additional;
        }

        return (float) BillingUsageEvent::where('tenant_id', $tenantId)
            ->where('meter_key', $meterKey)
            ->whereBetween('recorded_at', [$startOfMonth, $endOfMonth])
            ->sum('quantity');
    }

    /**
     * Check usage alert thresholds for a tenant against plan limits.
     *
     * @return array{meter: string, current: float, limit: ?int, percentage: float, alert: ?string}
     */
    public function evaluateThreshold(string $tenantId, string $meterKey, ?string $entitlementKey = null): array
    {
        $entKey = $entitlementKey ?? $meterKey . '_limit';
        $current = $this->getCurrentUsage($tenantId, $meterKey);
        $limit = $this->entitlementResolver->getLimit($tenantId, $entKey);

        $percentage = ($limit && $limit > 0) ? round(($current / $limit) * 100, 1) : 0.0;

        $alert = null;
        if ($limit && $limit > 0) {
            if ($percentage >= 100.0) {
                $alert = 'CRITICAL_100_PERCENT';
            } elseif ($percentage >= 90.0) {
                $alert = 'WARNING_90_PERCENT';
            } elseif ($percentage >= 80.0) {
                $alert = 'NOTICE_80_PERCENT';
            }
        }

        return [
            'meter' => $meterKey,
            'current' => $current,
            'limit' => $limit,
            'percentage' => $percentage,
            'alert' => $alert,
        ];
    }
}
