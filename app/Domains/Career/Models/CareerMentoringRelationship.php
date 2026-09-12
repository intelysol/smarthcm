<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'program_id', 'mentor_employee_id', 'mentee_employee_id',
    'start_date', 'end_date', 'matching_score', 'status', 'version'
])]
class CareerMentoringRelationship extends CareerModel
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'matching_score' => 'decimal:2',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<CareerMentoringProgram, CareerMentoringRelationship> */
    public function program(): BelongsTo
    {
        return $this->belongsTo(CareerMentoringProgram::class, 'program_id');
    }

    /** @return BelongsTo<Employee, CareerMentoringRelationship> */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentor_employee_id');
    }

    /** @return BelongsTo<Employee, CareerMentoringRelationship> */
    public function mentee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentee_employee_id');
    }

    /** @return HasMany<CareerMentoringGoal> */
    public function goals(): HasMany
    {
        return $this->hasMany(CareerMentoringGoal::class, 'relationship_id');
    }
}
