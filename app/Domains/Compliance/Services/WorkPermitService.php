<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WorkPermitService
{
    /**
     * Get all work permits for an employee.
     */
    public function getPermits(string $employeeId): Collection
    {
        return HcmEmployeeWorkPermit::where('employee_id', $employeeId)
            ->orderByDesc('is_current')
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * Add a work permit with effective dating and audit logging.
     */
    public function addPermit(string $employeeId, array $data, ?User $actor = null): HcmEmployeeWorkPermit
    {
        return DB::transaction(function () use ($employeeId, $data, $actor) {
            $employee = Employee::findOrFail($employeeId);
            $isCurrent = $data['is_current'] ?? true;
            $permitType = $data['permit_type'] ?? 'Standard Work Permit';

            if ($isCurrent) {
                HcmEmployeeWorkPermit::where('employee_id', $employeeId)
                    ->where('permit_type', $permitType)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            $effectiveFrom = isset($data['effective_from']) ? Carbon::parse($data['effective_from']) : Carbon::today();
            $expiryDate = Carbon::parse($data['expiry_date']);
            $status = $expiryDate->isPast() ? 'expired' : ($data['status'] ?? 'active');

            $permit = HcmEmployeeWorkPermit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'compliance_requirement_id' => $data['compliance_requirement_id'] ?? null,
                'permit_type' => $permitType,
                'permit_number' => $data['permit_number'],
                'issuing_authority' => $data['issuing_authority'] ?? null,
                'country' => $data['country'],
                'issue_date' => $data['issue_date'] ?? null,
                'effective_from' => $effectiveFrom->toDateString(),
                'expiry_date' => $expiryDate->toDateString(),
                'status' => $status,
                'verification_status' => $data['verification_status'] ?? 'unverified',
                'sponsor' => $data['sponsor'] ?? null,
                'job_restriction' => $data['job_restriction'] ?? null,
                'location_restriction' => $data['location_restriction'] ?? null,
                'notes' => $data['notes'] ?? null,
                'document_id' => $data['document_id'] ?? null,
                'is_current' => $isCurrent,
            ]);

            HcmComplianceAudit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'action' => 'work_permit.created',
                'entity_type' => HcmEmployeeWorkPermit::class,
                'entity_id' => $permit->id,
                'actor_id' => $actor?->id,
                'details' => ['permit_number' => $permit->permit_number, 'expiry' => $permit->expiry_date->toDateString()],
            ]);

            return $permit;
        });
    }

    /**
     * Update an existing work permit.
     */
    public function updatePermit(string $permitId, array $data, ?User $actor = null): HcmEmployeeWorkPermit
    {
        return DB::transaction(function () use ($permitId, $data, $actor) {
            $permit = HcmEmployeeWorkPermit::findOrFail($permitId);

            if (!empty($data['expiry_date'])) {
                $expiryDate = Carbon::parse($data['expiry_date']);
                if ($expiryDate->isPast()) {
                    $data['status'] = 'expired';
                }
            }

            $permit->update($data);

            HcmComplianceAudit::create([
                'tenant_id' => $permit->tenant_id,
                'employee_id' => $permit->employee_id,
                'action' => 'work_permit.updated',
                'entity_type' => HcmEmployeeWorkPermit::class,
                'entity_id' => $permit->id,
                'actor_id' => $actor?->id,
                'details' => array_keys($data),
            ]);

            return $permit->fresh();
        });
    }

    /**
     * Delete a work permit.
     */
    public function deletePermit(string $permitId): bool
    {
        $permit = HcmEmployeeWorkPermit::findOrFail($permitId);
        return (bool) $permit->delete();
    }
}
