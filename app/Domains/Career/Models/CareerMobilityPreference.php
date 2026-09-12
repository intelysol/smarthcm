<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'employee_id', 'mobility_type', 'preferred_department_id',
    'preferred_location', 'willing_to_relocate', 'remote_preference', 'notes'
])]
class CareerMobilityPreference extends CareerModel
{
    protected function casts(): array
    {
        return [
            'willing_to_relocate' => 'boolean',
        ];
    }

    /** @return BelongsTo<Employee, CareerMobilityPreference> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<Department, CareerMobilityPreference> */
    public function preferredDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'preferred_department_id');
    }
}
