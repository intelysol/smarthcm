<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmEmployeeLicense;
use App\Domains\Compliance\Models\HcmEmployeeRegistration;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProfessionalLicenseService
{
    /**
     * Get licenses for an employee.
     */
    public function getLicenses(string $employeeId): Collection
    {
        return HcmEmployeeLicense::where('employee_id', $employeeId)
            ->orderByDesc('is_current')
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * Add a professional/occupational license.
     */
    public function addLicense(string $employeeId, array $data, ?User $actor = null): HcmEmployeeLicense
    {
        return DB::transaction(function () use ($employeeId, $data, $actor) {
            $employee = Employee::findOrFail($employeeId);
            $isCurrent = $data['is_current'] ?? true;
            $licenseType = $data['license_type'] ?? 'Professional License';

            if ($isCurrent) {
                HcmEmployeeLicense::where('employee_id', $employeeId)
                    ->where('license_type', $licenseType)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            $effectiveFrom = isset($data['effective_from']) ? Carbon::parse($data['effective_from']) : Carbon::today();
            $expiryDate = isset($data['expiry_date']) ? Carbon::parse($data['expiry_date']) : null;
            $status = ($expiryDate && $expiryDate->isPast()) ? 'expired' : ($data['status'] ?? 'active');

            $license = HcmEmployeeLicense::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'compliance_requirement_id' => $data['compliance_requirement_id'] ?? null,
                'license_type' => $licenseType,
                'license_name' => $data['license_name'],
                'license_number' => $data['license_number'],
                'issuing_authority' => $data['issuing_authority'],
                'country' => $data['country'] ?? ($employee->country ?? 'USA'),
                'state_province' => $data['state_province'] ?? null,
                'issue_date' => $data['issue_date'] ?? null,
                'effective_from' => $effectiveFrom->toDateString(),
                'expiry_date' => $expiryDate?->toDateString(),
                'status' => $status,
                'verification_status' => $data['verification_status'] ?? 'unverified',
                'renewal_status' => $data['renewal_status'] ?? 'not_started',
                'restrictions' => $data['restrictions'] ?? null,
                'document_id' => $data['document_id'] ?? null,
                'is_current' => $isCurrent,
            ]);

            HcmComplianceAudit::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'action' => 'license.created',
                'entity_type' => HcmEmployeeLicense::class,
                'entity_id' => $license->id,
                'actor_id' => $actor?->id,
                'details' => ['license_name' => $license->license_name, 'license_number' => $license->license_number],
            ]);

            return $license;
        });
    }

    /**
     * Update an existing license.
     */
    public function updateLicense(string $licenseId, array $data, ?User $actor = null): HcmEmployeeLicense
    {
        return DB::transaction(function () use ($licenseId, $data, $actor) {
            $license = HcmEmployeeLicense::findOrFail($licenseId);

            if (!empty($data['expiry_date'])) {
                $expiryDate = Carbon::parse($data['expiry_date']);
                if ($expiryDate->isPast()) {
                    $data['status'] = 'expired';
                }
            }

            $license->update($data);

            HcmComplianceAudit::create([
                'tenant_id' => $license->tenant_id,
                'employee_id' => $license->employee_id,
                'action' => 'license.updated',
                'entity_type' => HcmEmployeeLicense::class,
                'entity_id' => $license->id,
                'actor_id' => $actor?->id,
                'details' => array_keys($data),
            ]);

            return $license->fresh();
        });
    }

    /**
     * Delete a license.
     */
    public function deleteLicense(string $licenseId): bool
    {
        $license = HcmEmployeeLicense::findOrFail($licenseId);
        return (bool) $license->delete();
    }

    /**
     * Add mandatory registration.
     */
    public function addRegistration(string $employeeId, array $data, ?User $actor = null): HcmEmployeeRegistration
    {
        $employee = Employee::findOrFail($employeeId);

        return HcmEmployeeRegistration::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employeeId,
            'compliance_requirement_id' => $data['compliance_requirement_id'] ?? null,
            'registration_type' => $data['registration_type'],
            'registration_number' => $data['registration_number'],
            'authority_name' => $data['authority_name'],
            'registration_date' => $data['registration_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'status' => $data['status'] ?? 'active',
            'verification_status' => $data['verification_status'] ?? 'unverified',
            'document_id' => $data['document_id'] ?? null,
        ]);
    }

    /**
     * Get registrations for an employee.
     */
    public function getRegistrations(string $employeeId): Collection
    {
        return HcmEmployeeRegistration::where('employee_id', $employeeId)->get();
    }
}
