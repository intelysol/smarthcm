<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeIdentifier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeIdentifierService
{
    public function __construct(
        protected PersonalDataSecurityService $securityService
    ) {}

    /**
     * Get all identifiers for an employee. Masked according to user permissions.
     */
    public function getIdentifiers(string $employeeId, ?User $actor = null): Collection
    {
        $identifiers = HcmEmployeeIdentifier::where('employee_id', $employeeId)
            ->orderBy('identifier_type')
            ->orderByDesc('is_primary')
            ->get();

        $canViewSensitive = $this->securityService->canViewSensitiveData($actor);

        if (!$canViewSensitive) {
            foreach ($identifiers as $id) {
                // Ensure identifier_value is masked in the output for unauthorized users
                $id->identifier_value = $id->masked_value;
            }
        }

        return $identifiers;
    }

    /**
     * Add an identifier. Automatically computes masked_value and stores encrypted identifier_value.
     */
    public function addIdentifier(string $employeeId, array $data, ?User $actor = null): HcmEmployeeIdentifier
    {
        return DB::transaction(function () use ($employeeId, $data) {
            $employee = Employee::findOrFail($employeeId);
            $type = $data['identifier_type'];
            $rawValue = $data['identifier_value'];
            $masked = $this->securityService->maskIdentifier($rawValue, $type);
            $isPrimary = $data['is_primary'] ?? false;

            $existingCount = HcmEmployeeIdentifier::where('employee_id', $employeeId)
                ->where('identifier_type', $type)
                ->count();

            if ($existingCount === 0) {
                $isPrimary = true;
            }

            if ($isPrimary) {
                HcmEmployeeIdentifier::where('employee_id', $employeeId)
                    ->where('identifier_type', $type)
                    ->update(['is_primary' => false]);
            }

            $identifier = HcmEmployeeIdentifier::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'identifier_type' => $type,
                'identifier_value' => $rawValue, // Casted to 'encrypted' on model
                'masked_value' => $masked,
                'issuing_country' => $data['issuing_country'] ?? null,
                'issue_date' => $data['issue_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'is_primary' => $isPrimary,
                'verification_status' => $data['verification_status'] ?? 'unverified',
            ]);

            if ($isPrimary) {
                $this->syncToEmployee($employee, $identifier, $rawValue);
            }

            return $identifier;
        });
    }

    /**
     * Update an identifier.
     */
    public function updateIdentifier(string $identifierId, array $data, ?User $actor = null): HcmEmployeeIdentifier
    {
        return DB::transaction(function () use ($identifierId, $data) {
            $identifier = HcmEmployeeIdentifier::findOrFail($identifierId);
            $employee = Employee::find($identifier->employee_id);

            if (!empty($data['identifier_value'])) {
                $data['masked_value'] = $this->securityService->maskIdentifier($data['identifier_value'], $identifier->identifier_type);
            }

            if (!empty($data['is_primary']) && $data['is_primary'] && !$identifier->is_primary) {
                HcmEmployeeIdentifier::where('employee_id', $identifier->employee_id)
                    ->where('identifier_type', $identifier->identifier_type)
                    ->where('id', '!=', $identifierId)
                    ->update(['is_primary' => false]);
            }

            $identifier->update($data);

            if ($employee && $identifier->is_primary) {
                $rawValue = $identifier->identifier_value;
                $this->syncToEmployee($employee, $identifier, $rawValue);
            }

            return $identifier->fresh();
        });
    }

    /**
     * Delete an identifier.
     */
    public function deleteIdentifier(string $identifierId): bool
    {
        $identifier = HcmEmployeeIdentifier::findOrFail($identifierId);
        return (bool) $identifier->delete();
    }

    /**
     * Find identifiers expiring within a given number of days.
     */
    public function getExpiringIdentifiers(string $tenantId, int $days = 30): Collection
    {
        $cutoff = Carbon::today()->addDays($days)->toDateString();
        $today = Carbon::today()->toDateString();

        return HcmEmployeeIdentifier::with('employee')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$today, $cutoff])
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Sync primary identifier to Core HR Employee.
     */
    protected function syncToEmployee(Employee $employee, HcmEmployeeIdentifier $identifier, string $value): void
    {
        $updates = [];
        if ($identifier->identifier_type === 'national_id') {
            $updates['national_id'] = $value;
        } elseif ($identifier->identifier_type === 'passport') {
            $updates['passport_number'] = $value;
            if ($identifier->expiry_date) {
                $updates['passport_expiry'] = $identifier->expiry_date;
            }
        } elseif ($identifier->identifier_type === 'driving_license') {
            $updates['driving_license_number'] = $value;
            if ($identifier->expiry_date) {
                $updates['driving_license_expiry'] = $identifier->expiry_date;
            }
        }

        if (!empty($updates)) {
            $employee->update($updates);
        }
    }
}
