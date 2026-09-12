<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VisaService
{
    /**
     * Get all visas and residency permits for an employee.
     */
    public function getVisas(string $employeeId): Collection
    {
        return HcmEmployeeVisaRecord::where('employee_id', $employeeId)
            ->orderByDesc('is_current')
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * Add a visa or residency permit record.
     */
    public function addVisa(string $employeeId, array $data, ?User $actor = null): HcmEmployeeVisaRecord
    {
        return DB::transaction(function () use ($employeeId, $data, $actor) {
            $employee = Employee::findOrFail($employeeId);
            $isCurrent = $data['is_current'] ?? true;
            $visaType = $data['visa_type'] ?? 'Employment Visa';

            if ($isCurrent) {
                HcmEmployeeVisaRecord::where('employee_id', $employeeId)
                    ->where('visa_type', $visaType)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            $effectiveFrom = isset($data['effective_from']) ? Carbon::parse($data['effective_from']) : Carbon::today();
            $expiryDate = Carbon::parse($data['expiry_date']);
            $status = $expiryDate->isPast() ? 'expired' : ($data['status'] ?? 'active');

            $visa = HcmEmployeeVisaRecord::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'compliance_requirement_id' => $data['compliance_requirement_id'] ?? null,
                'visa_type' => $visaType,
                'visa_number' => $data['visa_number'],
                'issuing_country' => $data['issuing_country'] ?? ($employee->country ?? 'USA'),
                'issuing_authority' => $data['issuing_authority'] ?? null,
                'issue_date' => $data['issue_date'] ?? null,
                'effective_from' => $effectiveFrom->toDateString(),
                'expiry_date' => $expiryDate->toDateString(),
                'entry_date' => $data['entry_date'] ?? null,
                'exit_date' => $data['exit_date'] ?? null,
                'is_multiple_entry' => $data['is_multiple_entry'] ?? false,
                'sponsor' => $data['sponsor'] ?? null,
                'residency_status' => $data['residency_status'] ?? null,
                'residency_number' => $data['residency_number'] ?? null,
                'status' => $status,
                'verification_status' => $data['verification_status'] ?? 'unverified',
                'notes' => $data['notes'] ?? null,
                'document_id' => $data['document_id'] ?? null,
                'is_current' => $isCurrent,
            ]);

            // Sync to Core HR Employee
            if ($isCurrent) {
                $employee->update([
                    'visa_number' => $visa->visa_number,
                    'visa_expiry' => $visa->expiry_date->toDateString(),
                ]);
            }

            HcmComplianceAudit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'action' => 'visa.created',
                'entity_type' => HcmEmployeeVisaRecord::class,
                'entity_id' => $visa->id,
                'actor_id' => $actor?->id,
                'details' => ['visa_number' => $visa->visa_number, 'expiry' => $visa->expiry_date->toDateString()],
            ]);

            return $visa;
        });
    }

    /**
     * Update an existing visa.
     */
    public function updateVisa(string $visaId, array $data, ?User $actor = null): HcmEmployeeVisaRecord
    {
        return DB::transaction(function () use ($visaId, $data, $actor) {
            $visa = HcmEmployeeVisaRecord::findOrFail($visaId);

            if (!empty($data['expiry_date'])) {
                $expiryDate = Carbon::parse($data['expiry_date']);
                if ($expiryDate->isPast()) {
                    $data['status'] = 'expired';
                }
            }

            $visa->update($data);

            if ($visa->is_current) {
                $employee = Employee::find($visa->employee_id);
                $employee?->update([
                    'visa_number' => $visa->visa_number,
                    'visa_expiry' => $visa->expiry_date->toDateString(),
                ]);
            }

            HcmComplianceAudit::create([
                'tenant_id' => $visa->tenant_id,
                'employee_id' => $visa->employee_id,
                'action' => 'visa.updated',
                'entity_type' => HcmEmployeeVisaRecord::class,
                'entity_id' => $visa->id,
                'actor_id' => $actor?->id,
                'details' => array_keys($data),
            ]);

            return $visa->fresh();
        });
    }

    /**
     * Delete a visa record.
     */
    public function deleteVisa(string $visaId): bool
    {
        $visa = HcmEmployeeVisaRecord::findOrFail($visaId);
        return (bool) $visa->delete();
    }
}
