<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Job;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'employee_id', 'current_job_id', 'rotation_job_id',
    'department_id', 'start_date', 'end_date', 'objective', 'status', 'version'
])]
class CareerJobRotation extends CareerModel
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, CareerJobRotation> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<Job, CareerJobRotation> */
    public function currentJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'current_job_id');
    }

    /** @return BelongsTo<Job, CareerJobRotation> */
    public function rotationJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'rotation_job_id');
    }

    /** @return BelongsTo<Department, CareerJobRotation> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
