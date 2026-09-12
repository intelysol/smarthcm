<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use Carbon\Carbon;

class EffectiveDatedChangeMonitoringService
{
    /**
     * Get monitored effective-dated changes partitioned by time horizons and backdated changes.
     */
    public function getEffectiveDatedChanges(string $tenantId): array
    {
        $today = Carbon::today();

        $baseQuery = PersonnelActionRequest::query()
            ->when($tenantId !== 'default', fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['employee', 'actionType', 'requester'])
            ->whereNotNull('effective_date');

        // 1. Effective Today
        $todayChanges = (clone $baseQuery)
            ->whereDate('effective_date', $today->toDateString())
            ->orderBy('effective_date', 'asc')
            ->get();

        // 2. Next 7 Days (excluding today)
        $next7Days = (clone $baseQuery)
            ->whereDate('effective_date', '>', $today->toDateString())
            ->whereDate('effective_date', '<=', $today->copy()->addDays(7)->toDateString())
            ->orderBy('effective_date', 'asc')
            ->get();

        // 3. Next 30 Days (excluding next 7 days)
        $next30Days = (clone $baseQuery)
            ->whereDate('effective_date', '>', $today->copy()->addDays(7)->toDateString())
            ->whereDate('effective_date', '<=', $today->copy()->addDays(30)->toDateString())
            ->orderBy('effective_date', 'asc')
            ->get();

        // 4. Next 90 Days (excluding next 30 days)
        $next90Days = (clone $baseQuery)
            ->whereDate('effective_date', '>', $today->copy()->addDays(30)->toDateString())
            ->whereDate('effective_date', '<=', $today->copy()->addDays(90)->toDateString())
            ->orderBy('effective_date', 'asc')
            ->get();

        // 5. Backdated Changes (requested or submitted after effective date)
        $backdatedChanges = (clone $baseQuery)
            ->where(function ($q) {
                $q->whereColumn('effective_date', '<', 'requested_at')
                    ->orWhere(function ($sub) {
                        $sub->whereDate('effective_date', '<', Carbon::today()->toDateString())
                            ->whereIn('status', ['draft', 'submitted', 'pending_approval']);
                    });
            })
            ->orderBy('effective_date', 'desc')
            ->get();

        return [
            'today' => $todayChanges,
            'next_7_days' => $next7Days,
            'next_30_days' => $next30Days,
            'next_90_days' => $next90Days,
            'backdated' => $backdatedChanges,
            'summary_counts' => [
                'today_count' => $todayChanges->count(),
                'next_7_days_count' => $next7Days->count(),
                'next_30_days_count' => $next30Days->count(),
                'next_90_days_count' => $next90Days->count(),
                'backdated_count' => $backdatedChanges->count(),
            ],
        ];
    }
}
