<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'employee_id', 'course_id', 'course_version_id',
    'provider_id', 'enrollment_id', 'certificate_id', 'completion_date',
    'final_score', 'credits_awarded', 'learning_hours', 'status'
])]
class EmployeeLearningRecord extends LearningModel
{
    protected function casts(): array
    {
        return [
            'completion_date' => 'date',
            'final_score' => 'decimal:2',
            'credits_awarded' => 'decimal:2',
            'learning_hours' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Employee, EmployeeLearningRecord> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningCourse, EmployeeLearningRecord> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningCourseVersion, EmployeeLearningRecord> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(LearningCourseVersion::class, 'course_version_id');
    }

    /** @return BelongsTo<LearningProvider, EmployeeLearningRecord> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(LearningProvider::class, 'provider_id');
    }

    /** @return BelongsTo<LearningEnrollment, EmployeeLearningRecord> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(LearningEnrollment::class, 'enrollment_id');
    }

    /** @return BelongsTo<LearningCertificate, EmployeeLearningRecord> */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(LearningCertificate::class, 'certificate_id');
    }
}
