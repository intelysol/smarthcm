<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityAssignmentBudget;
use App\Domains\Mobility\Models\MobilityAssignmentCost;
use App\Domains\Shared\Services\AuditService;

class MobilityCostEstimationService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Add or update an assignment cost item with multi-currency support.
     */
    public function addCostItem(MobilityAssignment $assignment, array $data): MobilityAssignmentCost
    {
        $sourceAmount = (float) $data['source_amount'];
        $exchangeRate = (float) ($data['exchange_rate'] ?? 1.0);
        $convertedAmount = $sourceAmount * $exchangeRate;

        $cost = MobilityAssignmentCost::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'cost_category' => $data['cost_category'],
            'cost_name' => $data['cost_name'],
            'source_amount' => $sourceAmount,
            'source_currency' => $data['source_currency'] ?? 'USD',
            'exchange_rate' => $exchangeRate,
            'exchange_rate_date' => $data['exchange_rate_date'] ?? now()->toDateString(),
            'converted_amount' => $convertedAmount,
            'converted_currency' => $data['converted_currency'] ?? $assignment->assignment_currency ?? 'USD',
            'frequency' => $data['frequency'] ?? 'one_time',
            'effective_from' => $data['effective_from'] ?? null,
            'effective_to' => $data['effective_to'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->syncBudgetForCategory($assignment, $cost->cost_category);

        return $cost;
    }

    /**
     * Sync budget actual/committed amounts for a specific cost category.
     */
    public function syncBudgetForCategory(MobilityAssignment $assignment, string $category): MobilityAssignmentBudget
    {
        $totalConverted = MobilityAssignmentCost::where('assignment_id', $assignment->id)
            ->where('cost_category', $category)
            ->sum('converted_amount');

        $budget = MobilityAssignmentBudget::firstOrNew([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'budget_category' => $category,
        ]);

        if (!$budget->exists) {
            $budget->approved_budget = $totalConverted;
            $budget->currency = $assignment->assignment_currency ?? 'USD';
        }

        $budget->committed_amount = $totalConverted;
        $budget->actual_amount = $totalConverted;
        $budget->variance_amount = (float) $budget->approved_budget - (float) $budget->actual_amount;
        $budget->save();

        return $budget;
    }

    /**
     * Get aggregated total costs for an assignment.
     */
    public function getTotalCostSummary(MobilityAssignment $assignment): array
    {
        $costs = MobilityAssignmentCost::where('assignment_id', $assignment->id)->get();

        $totalConverted = $costs->sum('converted_amount');
        $byCategory = $costs->groupBy('cost_category')->map(fn ($items) => $items->sum('converted_amount'));

        return [
            'assignment_id' => $assignment->id,
            'total_amount' => $totalConverted,
            'currency' => $assignment->assignment_currency ?? 'USD',
            'by_category' => $byCategory,
        ];
    }
}
