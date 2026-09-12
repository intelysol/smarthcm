<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitElection;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitWaiver;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BenefitWaiverService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function submitWaiver(Employee $employee, BenefitPlan $plan, array $data, ?User $actor = null): BenefitWaiver
    {
        if ($plan->is_mandatory) {
            throw ValidationException::withMessages([
                'plan' => "Plan '{$plan->name}' is mandatory by organizational policy and cannot be waived.",
            ]);
        }

        if (! $plan->is_waivable) {
            throw ValidationException::withMessages([
                'plan' => "Plan '{$plan->name}' does not allow waivers.",
            ]);
        }

        return DB::transaction(function () use ($employee, $plan, $data, $actor) {
            $waiver = BenefitWaiver::updateOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'benefit_plan_id' => $plan->id,
                    'benefit_enrollment_window_id' => $data['benefit_enrollment_window_id'] ?? null,
                ],
                [
                    'reason' => $data['reason'],
                    'supporting_document_id' => $data['supporting_document_id'] ?? null,
                    'waiver_date' => $data['waiver_date'] ?? now()->toDateString(),
                    'status' => 'submitted',
                ]
            );

            // Also create or update election as waived
            BenefitElection::updateOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'benefit_plan_id' => $plan->id,
                    'benefit_enrollment_window_id' => $data['benefit_enrollment_window_id'] ?? null,
                ],
                [
                    'coverage_level' => 'waived',
                    'election_date' => $waiver->waiver_date,
                    'effective_date' => $waiver->waiver_date,
                    'employee_cost_estimated' => 0,
                    'employer_cost_estimated' => 0,
                    'total_cost_estimated' => 0,
                    'currency' => $plan->currency ?? 'USD',
                    'status' => 'waived',
                    'is_waived' => true,
                    'waiver_reason' => $data['reason'],
                    'supporting_document_id' => $data['supporting_document_id'] ?? null,
                ]
            );

            $this->auditService->record(
                tenantId: $employee->tenant_id,
                eventType: 'benefit_waiver.submitted',
                action: 'submit',
                entityType: BenefitWaiver::class,
                entityId: $waiver->id,
                actorId: $actor?->id,
                after: $waiver->toArray()
            );

            return $waiver;
        });
    }

    public function approveWaiver(BenefitWaiver $waiver, User $approver): BenefitWaiver
    {
        $waiver->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->auditService->record(
            tenantId: $waiver->tenant_id,
            eventType: 'benefit_waiver.approved',
            action: 'approve',
            entityType: BenefitWaiver::class,
            entityId: $waiver->id,
            actorId: $approver->id,
            after: $waiver->toArray()
        );

        return $waiver;
    }

    public function rejectWaiver(BenefitWaiver $waiver, string $rejectionReason, User $approver): BenefitWaiver
    {
        $waiver->update([
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->auditService->record(
            tenantId: $waiver->tenant_id,
            eventType: 'benefit_waiver.rejected',
            action: 'reject',
            entityType: BenefitWaiver::class,
            entityId: $waiver->id,
            actorId: $approver->id,
            after: $waiver->toArray()
        );

        return $waiver;
    }

    public function getWaiversForEmployee(Employee $employee): Collection
    {
        return BenefitWaiver::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['plan', 'window', 'approver'])
            ->get();
    }
}
