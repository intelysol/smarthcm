<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'requirement_id', 'employee_id', 'course_id', 'enrollment_id',
    'status', 'assigned_at', 'due_at', 'completed_at', 'waived_at',
    'waiver_reason', 'waived_by'
])]
class LearningRequirementAssignment extends LearningModel
{
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'waived_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<LearningRequirement, LearningRequirementAssignment> */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(LearningRequirement::class, 'requirement_id');
    }

    /** @return BelongsTo<Employee, LearningRequirementAssignment> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningCourse, LearningRequirementAssignment> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningEnrollment, LearningRequirementAssignment> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(LearningEnrollment::class, 'enrollment_id');
    }

    /** @return BelongsTo<User, LearningRequirementAssignment> */
    public function waiverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }
}
