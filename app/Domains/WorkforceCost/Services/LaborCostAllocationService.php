<?php

namespace App\Domains\WorkforceCost\Services;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostAllocation;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostAllocationRule;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostAudit;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LaborCostAllocationService
{
    /**
     * Create an allocation rule with validation (total percentage == 100).
     */
    public function createRule(string $tenantId, array $data): HcmWorkforceCostAllocationRule
    {
        $targets = $data['targets'] ?? [];
        if ($data['allocation_method'] === 'percentage') {
            $sum = array_sum(array_column($targets, 'percentage'));
            if (abs($sum - 100.0) > 0.01) {
                throw new InvalidArgumentException("Allocation percentages must sum to 100%. Given sum: {$sum}%");
            }
        }

        return HcmWorkforceCostAllocationRule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'rule_name' => $data['rule_name'],
            'allocation_method' => $data['allocation_method'] ?? 'percentage',
            'source_type' => $data['source_type'] ?? 'department',
            'source_id' => $data['source_id'] ?? null,
            'targets' => $targets,
            'priority' => $data['priority'] ?? 10,
            'effective_from' => $data['effective_from'] ?? now()->toDateString(),
            'effective_to' => $data['effective_to'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Allocates a specific cost line according to applicable rule.
     */
    public function allocateCostLine(HcmWorkforceCostLine $line, ?HcmWorkforceCostAllocationRule $rule = null): Collection
    {
        $tenantId = $line->tenant_id;

        if (! $rule) {
            $rule = HcmWorkforceCostAllocationRule::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where('source_type', 'department')
                ->where('source_id', $line->department_id)
                ->orderBy('priority')
                ->first();
        }

        $results = collect();
        if (! $rule) {
            // Direct 100% allocation to line's native department/cost center
            $allocation = HcmWorkforceCostAllocation::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'cost_line_id' => $line->id,
                'target_department_id' => $line->department_id,
                'target_cost_center_id' => $line->cost_center_id,
                'allocated_percentage' => 100.00,
                'allocated_amount' => $line->amount,
                'currency' => $line->currency,
                'status' => 'allocated',
            ]);
            $results->push($allocation);
            return $results;
        }

        $targets = $rule->targets ?? [];
        foreach ($targets as $target) {
            $pct = (float) ($target['percentage'] ?? 100.0);
            $amount = round(((float) $line->amount * $pct) / 100.0, 4);

            $allocation = HcmWorkforceCostAllocation::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'cost_line_id' => $line->id,
                'allocation_rule_id' => $rule->id,
                'target_department_id' => $target['target_department_id'] ?? null,
                'target_cost_center_id' => $target['target_cost_center_id'] ?? null,
                'target_project_id' => $target['target_project_id'] ?? null,
                'allocated_percentage' => $pct,
                'allocated_amount' => $amount,
                'currency' => $line->currency,
                'status' => 'allocated',
            ]);
            $results->push($allocation);
        }

        // Record audit
        HcmWorkforceCostAudit::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'action_type' => 'cost_allocated',
            'entity_type' => HcmWorkforceCostLine::class,
            'entity_id' => $line->id,
            'new_state' => [
                'rule_id' => $rule->id,
                'allocations_count' => $results->count(),
                'total_allocated' => $line->amount,
            ],
        ]);

        return $results;
    }
}