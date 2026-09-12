<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'course_id', 'employee_id', 'instructor_id', 'session_id',
    'rating_course', 'rating_instructor', 'rating_content', 'rating_venue',
    'comments', 'is_private'
])]
class LearningFeedback extends LearningModel
{
    protected $table = 'learning_feedback';

    protected function casts(): array
    {
        return [
            'rating_course' => 'integer',
            'rating_instructor' => 'integer',
            'rating_content' => 'integer',
            'rating_venue' => 'integer',
            'is_private' => 'boolean',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningFeedback> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<Employee, LearningFeedback> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningInstructor, LearningFeedback> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(LearningInstructor::class, 'instructor_id');
    }

    /** @return BelongsTo<LearningSession, LearningFeedback> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id');
    }
}
