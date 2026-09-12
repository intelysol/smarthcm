<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['tenant_id', 'uuid', 'code', 'title', 'description', 'status', 'total_credits', 'completion_rule', 'min_required_credits', 'min_required_courses'])]
class LearningProgram extends LearningModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'total_credits' => 'decimal:2',
            'min_required_credits' => 'decimal:2',
            'min_required_courses' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $program): void {
            $program->uuid ??= (string) Str::uuid();
        });
    }

    /** @return HasMany<LearningProgramCourse> */
    public function programCourses(): HasMany
    {
        return $this->hasMany(LearningProgramCourse::class, 'program_id')->orderBy('sort_order');
    }

    /** @return BelongsToMany<LearningCourse> */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(LearningCourse::class, 'learning_program_courses', 'program_id', 'course_id')
            ->withPivot(['is_required', 'sort_order'])
            ->withTimestamps();
    }
}
