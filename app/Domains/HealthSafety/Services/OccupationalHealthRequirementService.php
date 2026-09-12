<?php

namespace App\Domains\HealthSafety\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\HealthSafety\Models\HcmEmployeeHealthRequirement;
use App\Domains\HealthSafety\Models\HcmHealthRequirement;
use App\Domains\HealthSafety\Models\HcmHealthRequirementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OccupationalHealthRequirementService
{
    /**
     * Create a health requirement definition.
     */
    public function createRequirement(string $tenantId, array $data, ?User $actor = null): HcmHealthRequirement
    {
        return DB::transaction(function () use ($tenantId, $data) {
            return HcmHealthRequirement::create(array_merge($data, [
                'tenant_id' => $tenantId,
                'version' => 1,
                'is_active' => $data['is_active'] ?? true,
            ]));
        });
    }

    /**
     * Update health requirement definition.
     */
    public function updateRequirement(string $id, array $data, ?User $actor = null): HcmHealthRequirement
    {
        $req = HcmHealthRequirement::findOrFail($id);
        $req->update($data);
        return $req->fresh();
    }

    /**
     * Evaluate applicable requirements for an employee based on job, department, location, exposure.
     */
    public function getApplicableRequirements(Employee $employee): Collection
    {
        $all = HcmHealthRequirement::where('tenant_id', $employee->tenant_id)
            ->where('is_active', true)
            ->get();

        return $all->filter(function (HcmHealthRequirement $req) use ($employee) {
            // Company filter
            if ($req->company_id && $req->company_id !== $employee->company_id) {
                return false;
            }
            // Branch filter
            if ($req->branch_id && $req->branch_id !== $employee->branch_id) {
                return false;
            }
            // Work location filter
            if ($req->work_location_id && $req->work_location_id !== $employee->work_location_id) {
                return false;
            }
            // Department filter
            if ($req->department_id && $req->department_id !== $employee->department_id) {
                return false;
            }
            // Position / Designation filter
            if ($req->designation_id && $req->designation_id !== $employee->designation_id) {
                return false;
            }
            // Country filter
            if ($req->country && $employee->country && strcasecmp($req->country, $employee->country) !== 0) {
                return false;
            }

            return true;
        });
    }

    /**
     * Synchronize and assign applicable requirements to an employee.
     */
    public function syncEmployeeRequirements(Employee $employee): int
    {
        $applicable = $this->getApplicableRequirements($employee);
        $assigned = 0;

        foreach ($applicable as $req) {
            $record = HcmEmployeeHealthRequirement::firstOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'health_requirement_id' => $req->id,
                ],
                [
                    'status' => 'required',
                    'due_date' => now()->addDays($req->reminder_days ?? 30)->toDateString(),
                ]
            );

            if ($record->wasRecentlyCreated) {
                $assigned++;
            }
        }

        return $assigned;
    }

    /**
     * Assign applicable requirements and return collection of assigned records.
     */
    public function assignApplicableRequirements(Employee $employee): Collection
    {
        $this->syncEmployeeRequirements($employee);

        return HcmEmployeeHealthRequirement::where('employee_id', $employee->id)
            ->with('healthRequirement')
            ->get();
    }

    /**
     * Get employee assigned health requirements.
     */
    public function getEmployeeRequirements(Employee $employee): Collection
    {
        return HcmEmployeeHealthRequirement::where('employee_id', $employee->id)
            ->with('healthRequirement')
            ->get();
    }
}

