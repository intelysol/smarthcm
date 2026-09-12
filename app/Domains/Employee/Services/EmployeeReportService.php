<?php

namespace App\Domains\Employee\Services;

use App\Domains\Employee\Models\Employee;

class EmployeeReportService
{
    public function dashboard(string $tenantId): array
    {
        $employees = Employee::query()->where('tenant_id', $tenantId);

        return [
            'total_employees' => (clone $employees)->count(),
            'active_employees' => (clone $employees)->where('employment_status', 'active')->count(),
            'new_hires' => (clone $employees)->whereDate('joining_date', '>=', now()->subDays(30)->toDateString())->count(),
            'employees_by_department' => (clone $employees)->selectRaw('department_id, count(*) as total')->groupBy('department_id')->pluck('total', 'department_id'),
            'employees_by_branch' => (clone $employees)->selectRaw('branch_id, count(*) as total')->groupBy('branch_id')->pluck('total', 'branch_id'),
            'employees_by_employment_type' => (clone $employees)->selectRaw('employment_type_id, count(*) as total')->groupBy('employment_type_id')->pluck('total', 'employment_type_id'),
            'gender_distribution' => (clone $employees)->selectRaw('gender, count(*) as total')->groupBy('gender')->pluck('total', 'gender'),
            'upcoming_birthdays' => (clone $employees)
                ->whereNotNull('date_of_birth')
                ->get(['id', 'first_name', 'last_name', 'date_of_birth'])
                ->sortBy(fn (Employee $employee): string => $employee->date_of_birth?->format('m-d') ?? '12-31')
                ->take(10)
                ->values(),
            'upcoming_confirmations' => (clone $employees)->whereBetween('confirmation_date', [now(), now()->addDays(30)])->get(['id', 'first_name', 'last_name', 'confirmation_date']),
            'visa_expiry' => (clone $employees)->whereBetween('visa_expiry', [now(), now()->addDays(60)])->count(),
            'passport_expiry' => (clone $employees)->whereBetween('passport_expiry', [now(), now()->addDays(180)])->count(),
            'contract_expiry' => (clone $employees)->whereBetween('contract_end_date', [now(), now()->addDays(60)])->count(),
        ];
    }

    public function masterList(string $tenantId): array
    {
        return Employee::query()
            ->where('tenant_id', $tenantId)
            ->with(['department', 'designation', 'branch'])
            ->orderBy('employee_number')
            ->get()
            ->toArray();
    }
}
