<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'succession_plan_id', 'position_id', 'job_id', 'department_id',
    'current_incumbent_id', 'criticality', 'vacancy_risk', 'retirement_risk',
    'risk_score', 'status', 'emergency_successor_id', 'interim_successor_id', 'version'
])]
class SuccessionPosition extends CareerModel
{
    protected function casts(): array
    {
        return [
            'risk_score' => 'decimal:2',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<SuccessionPlan, SuccessionPosition> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SuccessionPlan::class, 'succession_plan_id');
    }

    /** @return BelongsTo<Position, SuccessionPosition> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /** @return BelongsTo<Job, SuccessionPosition> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /** @return BelongsTo<Department, SuccessionPosition> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /** @return BelongsTo<Employee, SuccessionPosition> */
    public function incumbent(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_incumbent_id');
    }

    /** @return BelongsTo<Employee, SuccessionPosition> */
    public function emergencySuccessor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emergency_successor_id');
    }

    /** @return BelongsTo<Employee, SuccessionPosition> */
    public function interimSuccessor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'interim_successor_id');
    }

    /** @return HasMany<SuccessionCandidate> */
    public function candidates(): HasMany
    {
        return $this->hasMany(SuccessionCandidate::class, 'succession_position_id')->orderBy('priority');
    }
}
