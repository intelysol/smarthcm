<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\EnrollmentStatus;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BenefitsLifecycleIntegrationService
{
    public function __construct(
        protected BenefitEligibilityService $eligibilityService,
        protected AuditService $auditService
    ) {}

    /**
     * Triggered during Onboarding (Epic 2.27) when an employee is created/confirmed.
     */
    public function handleEmployeeOnboarding(Employee $employee): array
    {
        $eligiblePlans = $this->eligibilityService->getEligiblePlansForEmployee($employee);

        $this->auditService->record(
            tenantId: $employee->tenant_id,
            eventType: 'benefits.onboarding_evaluated',
            action: 'evaluate',
            entityType: Employee::class,
            entityId: $employee->id,
            after: ['eligible_plans_count' => $eligiblePlans->count()]
        );

        return [
            'employee_id' => $employee->id,
            'eligible_plans_count' => $eligiblePlans->count(),
            'plans' => $eligiblePlans->pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * Triggered when Personnel Actions (Epic 2.28) occur (Promotion, Transfer, Grade, Location).
     */
    public function handlePersonnelActionImpact(Employee $employee, array $changedFields): array
    {
        $tenantId = $employee->tenant_id;
        $plans = BenefitPlan::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $results = [];

        foreach ($plans as $plan) {
            $eval = $this->eligibilityService->evaluateEligibility($employee, $plan, null, true);
            $results[] = [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'status' => $eval->status,
                'reason' => $eval->reason,
            ];
        }

        $this->auditService->record(
            tenantId: $tenantId,
            eventType: 'benefits.personnel_action_reevaluated',
            action: 'reevaluate',
            entityType: Employee::class,
            entityId: $employee->id,
            before: ['changed_fields' => $changedFields],
            after: ['evaluations' => $results]
        );

        return $results;
    }

    /**
     * Triggered when Offboarding (Epic 2.29) occurs.
     * Ends coverage on effective termination date without deleting historical records.
     */
    public function handleEmployeeOffboarding(Employee $employee, string $separationDate, ?User $actor = null): int
    {
        $tenantId = $employee->tenant_id;
        $activeEnrollments = BenefitEnrollment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'active'])
            ->where(function ($q) use ($separationDate) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>', $separationDate);
            })
            ->get();

        $count = 0;
        DB::transaction(function () use ($activeEnrollments, $separationDate, $tenantId, $employee, $actor, &$count) {
            foreach ($activeEnrollments as $enr) {
                $before = $enr->toArray();
                $enr->update([
                    'effective_to' => $separationDate,
                    'status' => EnrollmentStatus::EXPIRED->value,
                    'notes' => trim(($enr->notes ?? '') . " [Terminated due to employee separation on {$separationDate}]"),
                ]);
                $count++;

                $this->auditService->record(
                    tenantId: $tenantId,
                    eventType: 'benefit_enrollment.terminated_on_separation',
                    action: 'terminate',
                    entityType: BenefitEnrollment::class,
                    entityId: $enr->id,
                    actorId: $actor?->id,
                    before: $before,
                    after: $enr->toArray()
                );
            }
        });

        return $count;
    }
}
