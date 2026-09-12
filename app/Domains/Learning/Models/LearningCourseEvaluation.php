<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'course_id', 'pre_score_avg', 'post_score_avg', 'feedback_score_avg', 'completion_rate', 'sample_size', 'calculated_at'])]
class LearningCourseEvaluation extends LearningModel
{
    protected function casts(): array
    {
        return [
            'pre_score_avg' => 'decimal:2',
            'post_score_avg' => 'decimal:2',
            'feedback_score_avg' => 'decimal:2',
            'completion_rate' => 'decimal:2',
            'sample_size' => 'integer',
            'calculated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningCourseEvaluation> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }
}
