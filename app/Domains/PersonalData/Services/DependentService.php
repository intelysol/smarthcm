<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeDependent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DependentService
{
    /**
     * Get all dependents for an employee.
     */
    public function getDependents(string $employeeId): Collection
    {
        return HcmEmployeeDependent::where('employee_id', $employeeId)
            ->orderBy('relationship')
            ->orderBy('date_of_birth')
            ->get();
    }

    /**
     * Add a dependent record.
     */
    public function addDependent(string $employeeId, array $data, ?User $actor = null): HcmEmployeeDependent
    {
        $employee = Employee::findOrFail($employeeId);

        $firstName = $data['first_name'] ?? null;
        $lastName = $data['last_name'] ?? null;
        $name = $data['name'] ?? trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
        if (empty($name)) {
            $name = 'Dependent';
        }

        return HcmEmployeeDependent::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employeeId,
            'name' => $name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'relationship' => $data['relationship'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'national_id_number' => $data['national_id_number'] ?? ($data['national_id'] ?? null),
            'is_disabled' => $data['is_disabled'] ?? false,
            'is_student' => $data['is_student'] ?? false,
            'contact_phone' => $data['contact_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a dependent record.
     */
    public function updateDependent(string $dependentId, array $data, ?User $actor = null): HcmEmployeeDependent
    {
        $dependent = HcmEmployeeDependent::findOrFail($dependentId);

        if (!isset($data['name']) && (isset($data['first_name']) || isset($data['last_name']))) {
            $data['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        }

        $dependent->update($data);

        return $dependent->fresh();
    }

    /**
     * Delete a dependent record.
     */
    public function deleteDependent(string $dependentId): bool
    {
        $dependent = HcmEmployeeDependent::findOrFail($dependentId);
        return (bool) $dependent->delete();
    }
}
