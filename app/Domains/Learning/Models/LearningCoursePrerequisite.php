<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'course_id', 'prerequisite_type', 'prerequisite_id', 'is_mandatory', 'min_grade_or_level'])]
class LearningCoursePrerequisite extends LearningModel
{
    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningCoursePrerequisite> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }
}
