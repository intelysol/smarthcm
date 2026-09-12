<?php

namespace App\Domains\Absence\Services;

use App\Domains\Absence\Models\HcmAbsencePeriod;
use App\Domains\Absence\Models\HcmAbsenceReturnToWorkPlan;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReturnToWorkService
{
    /**
     * Create an operational Return-to-Work plan (without confidential medical diagnoses).
     */
    public function createReturnPlan(
        string $tenantId,
        string $employeeId,
        Carbon $expectedReturnDate,
        ?string $absencePeriodId = null,
        string $returnPhase = 'phased_return',
        float $capacityPercentage = 50.00,
        array $operationalRestrictions = [],
        ?int $responsibleManagerId = null,
        ?int $hrOwnerId = null,
        ?string $notes = null
    ): HcmAbsenceReturnToWorkPlan {
        return HcmAbsenceReturnToWorkPlan::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'absence_period_id' => $absencePeriodId,
            'expected_return_date' => $expectedReturnDate->toDateString(),
            'return_phase' => $returnPhase,
            'capacity_percentage' => $capacityPercentage,
            'operational_restrictions' => $operationalRestrictions,
            'review_date' => $expectedReturnDate->copy()->addWeeks(2)->toDateString(),
            'responsible_manager_id' => $responsibleManagerId,
            'hr_owner_id' => $hrOwnerId,
            'status' => 'active',
            'notes' => $notes,
        ]);
    }

    /**
     * Progress return plan to next phase (e.g. 50% -> 75% -> 100% full duty).
     */
    public function progressReturnPlan(
        string $planId,
        string $newPhase,
        float $newCapacityPercentage,
        ?Carbon $actualReturnDate = null
    ): HcmAbsenceReturnToWorkPlan {
        $plan = HcmAbsenceReturnToWorkPlan::findOrFail($planId);

        $isCompleted = ($newPhase === 'full_duty' || $newCapacityPercentage >= 100.00);

        $plan->update([
            'return_phase' => $newPhase,
            'capacity_percentage' => $newCapacityPercentage,
            'actual_return_date' => $actualReturnDate?->toDateString() ?? ($isCompleted ? now()->toDateString() : $plan->actual_return_date),
            'status' => $isCompleted ? 'completed' : 'active',
        ]);

        if ($isCompleted && $plan->absence_period_id) {
            $period = HcmAbsencePeriod::find($plan->absence_period_id);
            $period?->update([
                'status' => 'returned',
                'actual_return_date' => $plan->actual_return_date ?? now()->toDateString(),
            ]);
        }

        return $plan;
    }
}