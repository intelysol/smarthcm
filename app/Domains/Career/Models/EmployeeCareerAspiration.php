<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Job;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'employee_id', 'target_job_id', 'target_career_level',
    'preferred_timeline_months', 'preferred_location', 'interest_areas',
    'preferences', 'visibility', 'notes', 'status', 'version'
])]
class EmployeeCareerAspiration extends CareerModel
{
    protected function casts(): array
    {
        return [
            'preferred_timeline_months' => 'integer',
            'interest_areas' => 'array',
            'preferences' => 'array',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, EmployeeCareerAspiration> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<Job, EmployeeCareerAspiration> */
    public function targetJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'target_job_id');
    }
}
