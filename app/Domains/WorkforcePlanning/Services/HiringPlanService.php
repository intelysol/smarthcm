<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\WorkforcePlanning\Enums\HiringPriority;
use App\Domains\WorkforcePlanning\Enums\ReplacementType;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceHiringPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePositionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HiringPlanService
{
    public function createHiringRequirement(HcmWorkforcePlan $plan, array $data): HcmWorkforceHiringPlan
    {
        if ($plan->status === 'locked') {
            throw ValidationException::withMessages(['plan' => 'Cannot add hiring requirements to a locked plan.']);
        }

        return HcmWorkforceHiringPlan::create([
            'tenant_id' => $plan->tenant_id,
            'plan_id' => $plan->id,
            'position_plan_id' => $data['position_plan_id'] ?? null,
            'title' => $data['title'],
            'department_id' => $data['department_id'] ?? null,
            'job_grade_id' => $data['job_grade_id'] ?? null,
            'planned_start_date' => $data['planned_start_date'],
            'priority' => $data['priority'] ?? HiringPriority::MEDIUM->value,
            'reason' => $data['reason'] ?? 'growth',
            'replacement_type' => $data['replacement_type'] ?? ReplacementType::NONE->value,
            'replaces_employee_id' => $data['replaces_employee_id'] ?? null,
            'status' => 'planned',
            'hiring_manager_id' => $data['hiring_manager_id'] ?? null,
        ]);
    }

    /**
     * Generates structured payload for recruitment requisition handoff without mutating candidate tables.
     */
    public function generateRecruitmentHandoffPayload(HcmWorkforceHiringPlan $hiringPlan): array
    {
        return [
            'tenant_id' => $hiringPlan->tenant_id,
            'hiring_plan_id' => $hiringPlan->id,
            'position_plan_id' => $hiringPlan->position_plan_id,
            'title' => $hiringPlan->title,
            'department_id' => $hiringPlan->department_id,
            'job_grade_id' => $hiringPlan->job_grade_id,
            'target_start_date' => $hiringPlan->planned_start_date->toDateString(),
            'priority' => $hiringPlan->priority,
            'justification' => $hiringPlan->reason,
            'replacement_type' => $hiringPlan->replacement_type,
            'hiring_manager_id' => $hiringPlan->hiring_manager_id,
            'status' => 'ready_for_recruitment',
        ];
    }
}
