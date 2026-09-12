<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'employee_id', 'course_id', 'program_id', 'path_id',
    'source', 'competency_id', 'recommended_by', 'reason', 'status'
])]
class LearningRecommendation extends LearningModel
{
    /** @return BelongsTo<Employee, LearningRecommendation> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningCourse, LearningRecommendation> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningProgram, LearningRecommendation> */
    public function program(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'program_id');
    }

    /** @return BelongsTo<LearningPath, LearningRecommendation> */
    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'path_id');
    }

    /** @return BelongsTo<Employee, LearningRecommendation> */
    public function recommender(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'recommended_by');
    }
}
