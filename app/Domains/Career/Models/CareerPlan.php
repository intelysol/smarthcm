<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Job;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'employee_id', 'current_job_id', 'target_job_id',
    'target_date', 'readiness_level', 'readiness_score', 'visibility',
    'status', 'approved_by', 'approved_at', 'version'
])]
class CareerPlan extends CareerModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'readiness_score' => 'decimal:2',
            'approved_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, CareerPlan> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<Job, CareerPlan> */
    public function currentJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'current_job_id');
    }

    /** @return BelongsTo<Job, CareerPlan> */
    public function targetJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'target_job_id');
    }

    /** @return BelongsTo<Employee, CareerPlan> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /** @return HasMany<CareerPlanAction> */
    public function actions(): HasMany
    {
        return $this->hasMany(CareerPlanAction::class, 'plan_id');
    }
}
