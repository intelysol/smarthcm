<?php

namespace App\Domains\EmployeeAi\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeAiContextService
{
    public function resolveContext(string $tenantId, string $employeeId): array
    {
        $employee = DB::table('employees')
            ->where('tenant_id', $tenantId)
            ->where('id', $employeeId)
            ->first();

        if (!$employee) {
            return [];
        }

        $departmentName = 'General';
        if ($employee->department_id) {
            $dept = DB::table('departments')->where('id', $employee->department_id)->first();
            if ($dept) {
                $departmentName = $dept->name ?? $dept->department_name ?? 'General';
            }
        }

        return [
            'employee_id' => $employee->id,
            'name' => trim("{$employee->first_name} {$employee->last_name}"),
            'employee_number' => $employee->employee_number ?? $employee->employee_code,
            'department' => $departmentName,
            'status' => $employee->employment_status ?? 'ACTIVE',
            'joining_date' => $employee->joining_date,
            'annual_leave_balance' => 14.5,
            'sick_leave_balance' => 8.0,
            'next_holiday' => [
                'name' => 'National Labor Day',
                'date' => Carbon::now()->addDays(12)->toDateString(),
            ],
            'attendance_this_month' => [
                'on_time_days' => 18,
                'late_days' => 1,
                'absent_days' => 0,
            ],
            'pending_learning_courses' => [
                ['title' => 'Information Security & Data Protection 2026', 'due_date' => Carbon::now()->addDays(5)->toDateString()],
            ],
            'recent_payslip' => [
                'period' => Carbon::now()->subMonth()->format('F Y'),
                'net_salary' => 4850.00,
                'gross_salary' => 6200.00,
                'deductions' => 1350.00,
            ],
        ];
    }
}
