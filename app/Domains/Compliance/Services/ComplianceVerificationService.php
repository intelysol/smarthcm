<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmComplianceVerification;
use App\Domains\Compliance\Models\HcmEmployeeLicense;
use App\Domains\Compliance\Models\HcmEmployeeRegistration;
use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ComplianceVerificationService
{
    public function __construct(
        protected ComplianceEvaluationService $evaluationService
    ) {}

    /**
     * Record a verification outcome for any compliance record.
     */
    public function recordVerification(
        string $employeeId,
        string $verifiableType,
        string $verifiableId,
        string $source,
        string $status,
        ?string $reference = null,
        ?string $notes = null,
        ?User $verifier = null
    ): HcmComplianceVerification {
        return DB::transaction(function () use (
            $employeeId, $verifiableType, $verifiableId, $source, $status, $reference, $notes, $verifier
        ) {
            $employee = Employee::findOrFail($employeeId);

            if (!in_array($status, ['pending', 'verified', 'rejected'])) {
                throw new InvalidArgumentException("Invalid verification status: {$status}");
            }

            $verification = HcmComplianceVerification::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'verifiable_type' => $verifiableType,
                'verifiable_id' => $verifiableId,
                'verification_source' => $source,
                'status' => $status,
                'verified_by' => $verifier?->id,
                'verified_at' => Carbon::now(),
                'verification_reference' => $reference,
                'verification_notes' => $notes,
            ]);

            // Update status on verifiable model
            $modelClass = match ($verifiableType) {
                'work_permit', HcmEmployeeWorkPermit::class => HcmEmployeeWorkPermit::class,
                'visa', HcmEmployeeVisaRecord::class => HcmEmployeeVisaRecord::class,
                'license', HcmEmployeeLicense::class => HcmEmployeeLicense::class,
                'registration', HcmEmployeeRegistration::class => HcmEmployeeRegistration::class,
                default => null,
            };

            if ($modelClass) {
                $target = $modelClass::find($verifiableId);
                $target?->update(['verification_status' => $status]);
            }

            HcmComplianceAudit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'action' => 'compliance.verified',
                'entity_type' => $verifiableType,
                'entity_id' => $verifiableId,
                'actor_id' => $verifier?->id,
                'details' => ['status' => $status, 'source' => $source],
            ]);

            // Refresh evaluation
            $this->evaluationService->evaluateEmployee($employeeId);

            return $verification;
        });
    }

    /**
     * Get verifications for an employee.
     */
    public function getVerifications(string $employeeId): Collection
    {
        return HcmComplianceVerification::where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get();
    }
}
