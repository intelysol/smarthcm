<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'course_id', 'title', 'description',
    'target_type', 'target_id', 'deadline_type', 'due_date', 'days_offset',
    'is_recurring', 'recurrence_interval_months', 'compliance_category', 'status'
])]
class LearningRequirement extends LearningModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'days_offset' => 'integer',
            'is_recurring' => 'boolean',
            'recurrence_interval_months' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $req): void {
            $req->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<LearningCourse, LearningRequirement> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return HasMany<LearningRequirementAssignment> */
    public function assignments(): HasMany
    {
        return $this->hasMany(LearningRequirementAssignment::class, 'requirement_id');
    }
}
