<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'course_id', 'course_version_id', 'title', 'description', 'sort_order'])]
class LearningCourseModule extends LearningModel
{
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningCourseModule> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningCourseVersion, LearningCourseModule> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(LearningCourseVersion::class, 'course_version_id');
    }

    /** @return HasMany<LearningCourseLesson> */
    public function lessons(): HasMany
    {
        return $this->hasMany(LearningCourseLesson::class, 'module_id')->orderBy('sort_order');
    }

    /** @return HasMany<LearningItem> */
    public function items(): HasMany
    {
        return $this->hasMany(LearningItem::class, 'module_id')->orderBy('sort_order');
    }
}
