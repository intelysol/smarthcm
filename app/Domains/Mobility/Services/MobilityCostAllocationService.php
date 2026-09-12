<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityCostAllocation;
use App\Domains\Mobility\Models\MobilityIntegrationRecord;
use App\Domains\Shared\Services\AuditService;
use InvalidArgumentException;

class MobilityCostAllocationService
{
    public function __construct(
        protected MobilityCostEstimationService $costEstimationService,
        protected AuditService $auditService
    ) {}

    /**
     * Allocate costs between Home, Host, or Shared entities.
     * Enforces that total allocation percentage equals 100%.
     *
     * @param MobilityAssignment $assignment
     * @param array $allocations Array of ['entity_role' => 'home'|'host'|'shared', 'company_id' => ..., 'department_id' => ..., 'cost_center_code' => ..., 'allocation_percentage' => ...]
     */
    public function allocateCosts(MobilityAssignment $assignment, array $allocations): array
    {
        $totalPercentage = collect($allocations)->sum('allocation_percentage');

        if (abs($totalPercentage - 100.0) > 0.01) {
            throw new InvalidArgumentException("Total cost allocation percentage must equal 100%. Provided: {$totalPercentage}%");
        }

        $summary = $this->costEstimationService->getTotalCostSummary($assignment);
        $totalCost = (float) $summary['total_amount'];

        // Remove previous allocations
        MobilityCostAllocation::where('assignment_id', $assignment->id)->delete();

        $createdAllocations = [];
        foreach ($allocations as $alloc) {
            $percentage = (float) $alloc['allocation_percentage'];
            $allocatedAmount = ($totalCost * $percentage) / 100.0;

            $created = MobilityCostAllocation::create([
                'tenant_id' => $assignment->tenant_id,
                'assignment_id' => $assignment->id,
                'entity_role' => $alloc['entity_role'],
                'company_id' => $alloc['company_id'],
                'department_id' => $alloc['department_id'] ?? null,
                'cost_center_code' => $alloc['cost_center_code'] ?? null,
                'allocation_percentage' => $percentage,
                'allocated_amount' => $allocatedAmount,
                'effective_from' => $alloc['effective_from'] ?? $assignment->start_date,
                'effective_to' => $alloc['effective_to'] ?? $assignment->planned_end_date,
            ]);

            $createdAllocations[] = $created;
        }

        // Stage finance integration record for intercompany billing / GL posting
        MobilityIntegrationRecord::create([
            'tenant_id' => $assignment->tenant_id,
            'integration_target' => 'finance_gl',
            'entity_type' => 'MobilityCostAllocation',
            'entity_id' => $assignment->id,
            'status' => 'staged',
            'payload' => [
                'assignment_number' => $assignment->assignment_number,
                'total_cost' => $totalCost,
                'currency' => $assignment->assignment_currency ?? 'USD',
                'allocations' => collect($createdAllocations)->map(fn ($a) => [
                    'entity_role' => $a->entity_role,
                    'company_id' => $a->company_id,
                    'cost_center' => $a->cost_center_code,
                    'percentage' => $a->allocation_percentage,
                    'amount' => $a->allocated_amount,
                ])->toArray(),
            ],
        ]);

        return $createdAllocations;
    }
}
