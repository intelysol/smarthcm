<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id', 'course_id', 'module_id', 'lesson_id', 'item_type',
    'title', 'content_id', 'assessment_id', 'sort_order', 'is_mandatory',
    'completion_rule', 'min_duration_seconds'
])]
class LearningItem extends LearningModel
{
    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'sort_order' => 'integer',
            'min_duration_seconds' => 'integer',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningItem> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningCourseModule, LearningItem> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningCourseModule::class, 'module_id');
    }

    /** @return BelongsTo<LearningCourseLesson, LearningItem> */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LearningCourseLesson::class, 'lesson_id');
    }

    /** @return BelongsTo<LearningContent, LearningItem> */
    public function content(): BelongsTo
    {
        return $this->belongsTo(LearningContent::class, 'content_id');
    }

    /** @return BelongsTo<LearningAssessment, LearningItem> */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(LearningAssessment::class, 'assessment_id');
    }

    /** @return HasMany<LearningProgress> */
    public function progressRecords(): HasMany
    {
        return $this->hasMany(LearningProgress::class, 'learning_item_id');
    }
}
