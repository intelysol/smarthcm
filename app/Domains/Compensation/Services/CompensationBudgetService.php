<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

use App\Domains\Compensation\Models\CompensationBudget;
use App\Domains\Compensation\Models\CompensationCycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CompensationBudgetService
{
    public function allocate(
        User $user,
        CompensationCycle $cycle,
        string $scopeType,
        string $scopeId,
        float $allocatedAmount,
        string $budgetType = 'merit',
        float $holdbackAmount = 0.0,
        ?string $currency = null
    ): CompensationBudget {
        if ($allocatedAmount < 0.0) {
            throw ValidationException::withMessages([
                'allocated' => 'Allocated budget amount cannot be negative.',
            ]);
        }

        return CompensationBudget::updateOrCreate(
            [
                'tenant_id' => $user->tenant_id,
                'compensation_cycle_id' => $cycle->id,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'budget_type' => $budgetType,
            ],
            [
                'currency' => $currency ?? $cycle->currency ?? 'USD',
                'allocated' => $allocatedAmount,
                'holdback_amount' => $holdbackAmount,
            ]
        );
    }

    public function recordConsumption(CompensationBudget $budget, float $additionalAmount): CompensationBudget
    {
        $newConsumed = (float) $budget->consumed + $additionalAmount;
        $budget->update(['consumed' => $newConsumed]);

        return $budget->fresh();
    }

    public function getCycleBudgets(CompensationCycle $cycle): Collection
    {
        return $cycle->budgets()->get();
    }

    public function calculateRollup(CompensationCycle $cycle): array
    {
        $budgets = $cycle->budgets()->get();

        $totalAllocated = $budgets->sum('allocated');
        $totalConsumed = $budgets->sum('consumed');
        $totalHoldback = $budgets->sum('holdback_amount');
        $totalRemaining = $totalAllocated - $totalConsumed - $totalHoldback;
        $utilization = $totalAllocated > 0 ? round(($totalConsumed / $totalAllocated) * 100, 2) : 0.0;

        return [
            'total_allocated' => (float) $totalAllocated,
            'total_consumed' => (float) $totalConsumed,
            'total_holdback' => (float) $totalHoldback,
            'total_remaining' => (float) $totalRemaining,
            'utilization_percentage' => $utilization,
            'is_over_budget' => $totalConsumed > $totalAllocated,
        ];
    }
}
