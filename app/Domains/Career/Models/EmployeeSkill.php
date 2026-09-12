<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'employee_id', 'skill_id', 'current_level', 'target_level',
    'source', 'verification_status', 'verified_by', 'verified_at',
    'last_assessed_at', 'next_assessment_at', 'notes', 'version'
])]
class EmployeeSkill extends CareerModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'current_level' => 'integer',
            'target_level' => 'integer',
            'verified_at' => 'datetime',
            'last_assessed_at' => 'date',
            'next_assessment_at' => 'date',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, EmployeeSkill> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<CareerSkill, EmployeeSkill> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(CareerSkill::class, 'skill_id');
    }

    /** @return BelongsTo<Employee, EmployeeSkill> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }

    /** @return HasMany<EmployeeSkillEvidence> */
    public function evidence(): HasMany
    {
        return $this->hasMany(EmployeeSkillEvidence::class, 'employee_skill_id');
    }
}
