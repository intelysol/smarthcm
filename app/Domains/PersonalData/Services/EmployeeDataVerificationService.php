<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeDataVerification;
use App\Domains\PersonalData\Models\HcmEmployeeIdentifier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EmployeeDataVerificationService
{
    /**
     * Initiate a verification request.
     */
    public function initiateVerification(
        string $employeeId,
        string $verifiableType,
        string $verifiableId,
        string $method = 'hr_manual'
    ): HcmEmployeeDataVerification {
        $employee = Employee::findOrFail($employeeId);

        return HcmEmployeeDataVerification::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employeeId,
            'verifiable_type' => $verifiableType,
            'verifiable_id' => $verifiableId,
            'verification_method' => $method,
            'status' => 'pending',
        ]);
    }

    /**
     * Complete verification (verify, reject, or expire).
     */
    public function completeVerification(
        string $verificationId,
        string $status,
        ?string $notes = null,
        ?User $verifier = null
    ): HcmEmployeeDataVerification {
        return DB::transaction(function () use ($verificationId, $status, $notes, $verifier) {
            $verification = HcmEmployeeDataVerification::findOrFail($verificationId);

            if (!in_array($status, ['verified', 'rejected', 'expired'])) {
                throw new InvalidArgumentException("Invalid verification status: {$status}");
            }

            $verification->update([
                'status' => $status,
                'verified_by' => $verifier?->id,
                'verified_at' => Carbon::now(),
                'notes' => $notes,
            ]);

            // If target is identifier, update identifier status
            if ($verification->verifiable_type === 'identifier' || $verification->verifiable_type === HcmEmployeeIdentifier::class) {
                $identifier = HcmEmployeeIdentifier::find($verification->verifiable_id);
                if ($identifier) {
                    $identifier->update(['verification_status' => $status]);
                }
            }

            return $verification->fresh();
        });
    }

    /**
     * Get verifications for an employee.
     */
    public function getVerificationsForEmployee(string $employeeId): Collection
    {
        return HcmEmployeeDataVerification::where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get();
    }
}
