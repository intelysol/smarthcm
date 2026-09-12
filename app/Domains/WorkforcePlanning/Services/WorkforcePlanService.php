<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\WorkforcePlanning\Enums\WorkforcePlanStatus;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlanPeriod;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlanSnapshot;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlanVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkforcePlanService
{
    public function createPlan(array $data, ?int $userId = null): HcmWorkforcePlan
    {
        return DB::transaction(function () use ($data, $userId) {
            $plan = HcmWorkforcePlan::create([
                'tenant_id' => $data['tenant_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'planning_cycle' => $data['planning_cycle'] ?? 'Annual',
                'planning_type' => $data['planning_type'] ?? 'annual',
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => WorkforcePlanStatus::DRAFT->value,
                'current_version' => 1,
                'currency' => $data['currency'] ?? 'USD',
                'company_id' => $data['company_id'] ?? null,
                'business_unit_id' => $data['business_unit_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'owner_id' => $userId,
                'description' => $data['description'] ?? null,
            ]);

            // Auto-initialize monthly planning periods
            $start = Carbon::parse($plan->start_date)->startOfMonth();
            $end = Carbon::parse($plan->end_date)->endOfMonth();
            $curr = $start->copy();
            $seq = 1;

            while ($curr->lte($end)) {
                HcmWorkforcePlanPeriod::create([
                    'tenant_id' => $plan->tenant_id,
                    'plan_id' => $plan->id,
                    'period_name' => $curr->format('M Y'),
                    'period_sequence' => $seq++,
                    'start_date' => $curr->copy()->startOfMonth()->toDateString(),
                    'end_date' => $curr->copy()->endOfMonth()->toDateString(),
                    'is_closed' => false,
                ]);
                $curr->addMonth();
            }

            // Create Initial Version Record
            HcmWorkforcePlanVersion::create([
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'version_number' => 1,
                'status' => WorkforcePlanStatus::DRAFT->value,
                'change_rationale' => 'Initial plan creation.',
                'created_by' => $userId,
            ]);

            return $plan;
        });
    }

    public function submitPlan(HcmWorkforcePlan $plan, ?int $userId = null): HcmWorkforcePlan
    {
        if ($plan->status === WorkforcePlanStatus::LOCKED->value) {
            throw ValidationException::withMessages(['plan' => 'Cannot submit a locked plan. Create a new version first.']);
        }

        $plan->update(['status' => WorkforcePlanStatus::UNDER_REVIEW->value]);
        return $plan;
    }

    public function approvePlan(HcmWorkforcePlan $plan, ?int $userId = null): HcmWorkforcePlan
    {
        if ($plan->status === WorkforcePlanStatus::LOCKED->value) {
            throw ValidationException::withMessages(['plan' => 'Plan is already locked.']);
        }

        $plan->update(['status' => WorkforcePlanStatus::APPROVED->value]);
        return $plan;
    }

    public function lockPlan(HcmWorkforcePlan $plan, ?int $userId = null): HcmWorkforcePlan
    {
        return DB::transaction(function () use ($plan, $userId) {
            $plan->update([
                'status' => WorkforcePlanStatus::LOCKED->value,
                'locked_at' => now(),
                'locked_by' => $userId,
            ]);

            // Create immutable plan snapshot
            $fullState = [
                'plan' => $plan->toArray(),
                'periods' => $plan->periods()->get()->toArray(),
                'assumptions' => $plan->assumptions()->get()->toArray(),
                'headcount_plans' => $plan->headcountPlans()->get()->toArray(),
                'positions' => $plan->positions()->with('budget')->get()->toArray(),
                'hiring_plans' => $plan->hiringPlans()->get()->toArray(),
                'cost_plans' => $plan->costPlans()->get()->toArray(),
            ];

            HcmWorkforcePlanSnapshot::create([
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'version_number' => $plan->current_version,
                'full_plan_state' => $fullState,
                'frozen_at' => now(),
                'frozen_by' => $userId,
            ]);

            return $plan;
        });
    }

    public function createNewVersion(HcmWorkforcePlan $plan, string $changeRationale, ?int $userId = null): HcmWorkforcePlan
    {
        return DB::transaction(function () use ($plan, $changeRationale, $userId) {
            $newVersionNumber = $plan->current_version + 1;

            $plan->update([
                'current_version' => $newVersionNumber,
                'status' => WorkforcePlanStatus::OPEN->value,
                'locked_at' => null,
                'locked_by' => null,
            ]);

            HcmWorkforcePlanVersion::create([
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'version_number' => $newVersionNumber,
                'status' => WorkforcePlanStatus::OPEN->value,
                'change_rationale' => $changeRationale,
                'created_by' => $userId,
            ]);

            return $plan;
        });
    }
}
