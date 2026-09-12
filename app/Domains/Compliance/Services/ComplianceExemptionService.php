<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmComplianceExemption;
use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ComplianceExemptionService
{
    public function __construct(
        protected ComplianceEvaluationService $evaluationService
    ) {}

    /**
     * Request a compliance exemption / waiver.
     */
    public function requestExemption(
        string $employeeId,
        string $requirementId,
        string $reason,
        string $effectiveFrom,
        string $expiryDate,
        ?string $supportingDocId = null,
        ?User $actor = null
    ): HcmComplianceExemption {
        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Exemption reason is strictly required.');
        }

        return DB::transaction(function () use (
            $employeeId, $requirementId, $reason, $effectiveFrom, $expiryDate, $supportingDocId, $actor
        ) {
            $employee = Employee::findOrFail($employeeId);
            $requirement = HcmComplianceRequirement::findOrFail($requirementId);

            $exemption = HcmComplianceExemption::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'requirement_id' => $requirementId,
                'reason' => $reason,
                'status' => 'requested',
                'effective_from' => Carbon::parse($effectiveFrom)->toDateString(),
                'expiry_date' => Carbon::parse($expiryDate)->toDateString(),
                'supporting_document_id' => $supportingDocId,
            ]);

            HcmComplianceAudit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'action' => 'exemption.requested',
                'entity_type' => HcmComplianceExemption::class,
                'entity_id' => $exemption->id,
                'actor_id' => $actor?->id,
                'details' => ['requirement_name' => $requirement->name, 'reason' => $reason],
            ]);

            return $exemption;
        });
    }

    /**
     * Approve an exemption.
     */
    public function approveExemption(string $exemptionId, User $approver): HcmComplianceExemption
    {
        return DB::transaction(function () use ($exemptionId, $approver) {
            $exemption = HcmComplianceExemption::findOrFail($exemptionId);

            $exemption->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
            ]);

            HcmComplianceAudit::create([
                'tenant_id' => $exemption->tenant_id,
                'employee_id' => $exemption->employee_id,
                'action' => 'exemption.approved',
                'entity_type' => HcmComplianceExemption::class,
                'entity_id' => $exemption->id,
                'actor_id' => $approver->id,
            ]);

            // Re-evaluate employee compliance
            $this->evaluationService->evaluateEmployee($exemption->employee_id);

            return $exemption->fresh();
        });
    }

    /**
     * Reject an exemption.
     */
    public function rejectExemption(string $exemptionId, User $approver, ?string $notes = null): HcmComplianceExemption
    {
        return DB::transaction(function () use ($exemptionId, $approver, $notes) {
            $exemption = HcmComplianceExemption::findOrFail($exemptionId);

            $exemption->update([
                'status' => 'rejected',
                'approved_by' => $approver->id,
                'notes' => $notes,
            ]);

            HcmComplianceAudit::create([
                'tenant_id' => $exemption->tenant_id,
                'employee_id' => $exemption->employee_id,
                'action' => 'exemption.rejected',
                'entity_type' => HcmComplianceExemption::class,
                'entity_id' => $exemption->id,
                'actor_id' => $approver->id,
                'details' => ['notes' => $notes],
            ]);

            return $exemption->fresh();
        });
    }

    /**
     * Get exemptions for an employee.
     */
    public function getExemptions(string $employeeId): Collection
    {
        return HcmComplianceExemption::with('requirement')
            ->where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get();
    }
}
