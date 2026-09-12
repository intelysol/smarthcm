<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmComplianceRenewal;
use App\Domains\Compliance\Models\HcmEmployeeLicense;
use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ComplianceRenewalService
{
    public function __construct(
        protected ComplianceEvaluationService $evaluationService
    ) {}

    /**
     * Initiate a renewal cycle for an expiring permit, visa, or license.
     */
    public function initiateRenewal(
        string $employeeId,
        string $renewableType,
        string $renewableId,
        ?User $actor = null
    ): HcmComplianceRenewal {
        return DB::transaction(function () use ($employeeId, $renewableType, $renewableId, $actor) {
            $employee = Employee::findOrFail($employeeId);

            $modelClass = match ($renewableType) {
                'work_permit', HcmEmployeeWorkPermit::class => HcmEmployeeWorkPermit::class,
                'visa', HcmEmployeeVisaRecord::class => HcmEmployeeVisaRecord::class,
                'license', HcmEmployeeLicense::class => HcmEmployeeLicense::class,
                default => null,
            };

            $oldExpiry = null;
            if ($modelClass) {
                $target = $modelClass::find($renewableId);
                $oldExpiry = $target?->expiry_date?->toDateString();
            }

            $renewal = HcmComplianceRenewal::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'renewable_type' => $renewableType,
                'renewable_id' => $renewableId,
                'status' => 'initiated',
                'old_expiry_date' => $oldExpiry,
                'initiated_at' => Carbon::now(),
                'initiated_by' => $actor?->id,
            ]);

            HcmComplianceAudit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'action' => 'renewal.initiated',
                'entity_type' => $renewableType,
                'entity_id' => $renewableId,
                'actor_id' => $actor?->id,
            ]);

            return $renewal;
        });
    }

    /**
     * Progress renewal to documents submitted.
     */
    public function submitRenewalDocument(string $renewalId, ?string $notes = null): HcmComplianceRenewal
    {
        $renewal = HcmComplianceRenewal::findOrFail($renewalId);
        $renewal->update([
            'status' => 'document_submitted',
            'notes' => $notes,
        ]);

        return $renewal->fresh();
    }

    /**
     * Complete renewal with new expiry date.
     */
    public function completeRenewal(string $renewalId, string $newExpiryDate, ?User $actor = null): HcmComplianceRenewal
    {
        return DB::transaction(function () use ($renewalId, $newExpiryDate, $actor) {
            $renewal = HcmComplianceRenewal::findOrFail($renewalId);

            $renewal->update([
                'status' => 'completed',
                'new_expiry_date' => $newExpiryDate,
                'completed_at' => Carbon::now(),
            ]);

            // Update underlying model
            $modelClass = match ($renewal->renewable_type) {
                'work_permit', HcmEmployeeWorkPermit::class => HcmEmployeeWorkPermit::class,
                'visa', HcmEmployeeVisaRecord::class => HcmEmployeeVisaRecord::class,
                'license', HcmEmployeeLicense::class => HcmEmployeeLicense::class,
                default => null,
            };

            if ($modelClass) {
                $target = $modelClass::find($renewal->renewable_id);
                if ($target) {
                    $target->update([
                        'expiry_date' => $newExpiryDate,
                        'status' => 'active',
                    ]);
                }
            }

            HcmComplianceAudit::create([
                'tenant_id' => $renewal->tenant_id,
                'employee_id' => $renewal->employee_id,
                'action' => 'renewal.completed',
                'entity_type' => $renewal->renewable_type,
                'entity_id' => $renewal->renewable_id,
                'actor_id' => $actor?->id,
                'details' => ['new_expiry_date' => $newExpiryDate],
            ]);

            // Refresh evaluation
            $this->evaluationService->evaluateEmployee($renewal->employee_id);

            return $renewal->fresh();
        });
    }

    /**
     * Get renewals for an employee.
     */
    public function getRenewals(string $employeeId): Collection
    {
        return HcmComplianceRenewal::where('employee_id', $employeeId)
            ->orderByDesc('initiated_at')
            ->get();
    }
}
