<?php

namespace App\Domains\Employee\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\Employment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmploymentLifecycleService
{
    /** @param array<string,mixed> $attributes */
    public function rehire(Employee $employee, array $attributes): Employment
    {
        return DB::transaction(function () use ($employee, $attributes): Employment {
            $employment = Employment::query()->create([...$attributes, 'tenant_id' => $employee->tenant_id, 'employee_id' => $employee->id, 'employment_number' => $attributes['employment_number'] ?? 'EMPLOY-'.Str::upper(Str::random(10)), 'status' => 'active', 'effective_from' => $attributes['effective_from'] ?? $attributes['start_date']]);
            $employee->update(['current_employment_id' => $employment->id, 'employment_status' => 'active', 'termination_date' => null, 'original_hire_date' => $employee->original_hire_date ?? $employment->start_date, 'version' => $employee->version + 1]);
            return $employment;
        });
    }
}
