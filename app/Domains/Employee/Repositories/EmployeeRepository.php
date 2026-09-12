<?php

namespace App\Domains\Employee\Repositories;

use App\Domains\Employee\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EmployeeRepository
{
    public function paginate(string $tenantId, array $filters): LengthAwarePaginator
    {
        $query = Employee::query()
            ->where('tenant_id', $tenantId)
            ->with(['department', 'designation', 'branch', 'reportingManager']);

        if (($filters['search'] ?? null) !== null) {
            $search = (string) $filters['search'];
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('employee_number', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('passport_number', 'like', "%{$search}%")
                    ->orWhere('official_email', 'like', "%{$search}%")
                    ->orWhere('personal_email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        foreach (['company_id', 'branch_id', 'department_id', 'designation_id', 'employment_type_id', 'employment_status', 'gender'] as $column) {
            if (($filters[$column] ?? null) !== null && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        return $query->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findForTenant(string $tenantId, string $id): Employee
    {
        return Employee::query()
            ->where('tenant_id', $tenantId)
            ->with([
                'company', 'branch', 'businessUnit', 'department', 'section', 'team', 'costCenter',
                'designation', 'jobGrade', 'reportingManager', 'employmentType', 'workLocation',
                'shift', 'holidayCalendar', 'emergencyContacts', 'familyMembers', 'educations',
                'workExperiences', 'skills', 'certifications', 'languages', 'bankAccounts',
                'salaries', 'documents', 'customFieldValues.definition', 'timelines',
            ])
            ->findOrFail($id);
    }

    public function create(array $attributes): Employee
    {
        return Employee::query()->create($attributes);
    }

    public function update(Employee $employee, array $attributes): Employee
    {
        $employee->fill($attributes);
        $employee->save();

        return $employee->refresh();
    }

    public function nextEmployeeNumber(string $tenantId): string
    {
        DB::table('employee_number_sequences')->insertOrIgnore([
            'tenant_id' => $tenantId,
            'last_number' => 0,
        ]);

        $sequence = DB::table('employee_number_sequences')
            ->where('tenant_id', $tenantId)
            ->lockForUpdate()
            ->firstOrFail();

        $number = $sequence->last_number + 1;

        DB::table('employee_number_sequences')
            ->where('tenant_id', $tenantId)
            ->update(['last_number' => $number]);

        return 'EMP-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }
}
