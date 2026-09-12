<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'course_id', 'title', 'description',
    'assessment_type', 'scoring_method', 'total_points', 'passing_percentage',
    'time_limit_minutes', 'max_attempts', 'randomize_questions', 'status'
])]
class LearningAssessment extends LearningModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'total_points' => 'decimal:2',
            'passing_percentage' => 'decimal:2',
            'time_limit_minutes' => 'integer',
            'max_attempts' => 'integer',
            'randomize_questions' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $assessment): void {
            $assessment->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<LearningCourse, LearningAssessment> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return HasMany<LearningQuestion> */
    public function questions(): HasMany
    {
        return $this->hasMany(LearningQuestion::class, 'assessment_id')->orderBy('sort_order');
    }

    /** @return HasMany<LearningAssessmentAttempt> */
    public function attempts(): HasMany
    {
        return $this->hasMany(LearningAssessmentAttempt::class, 'assessment_id');
    }
}
