<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id', 'assessment_id', 'employee_id', 'enrollment_id',
    'attempt_number', 'started_at', 'submitted_at', 'score_obtained',
    'score_percentage', 'passed', 'status'
])]
class LearningAssessmentAttempt extends LearningModel
{
    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score_obtained' => 'decimal:2',
            'score_percentage' => 'decimal:2',
            'passed' => 'boolean',
        ];
    }

    /** @return BelongsTo<LearningAssessment, LearningAssessmentAttempt> */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(LearningAssessment::class, 'assessment_id');
    }

    /** @return BelongsTo<Employee, LearningAssessmentAttempt> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningEnrollment, LearningAssessmentAttempt> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(LearningEnrollment::class, 'enrollment_id');
    }

    /** @return HasMany<LearningAssessmentAnswer> */
    public function answers(): HasMany
    {
        return $this->hasMany(LearningAssessmentAnswer::class, 'attempt_id');
    }
}
