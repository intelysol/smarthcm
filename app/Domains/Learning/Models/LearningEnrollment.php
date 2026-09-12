<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'employee_id', 'course_id', 'course_version_id',
    'program_id', 'path_id', 'session_id', 'enrollment_type', 'status',
    'progress_percentage', 'enrolled_at', 'started_at', 'completed_at',
    'due_date', 'score', 'passed', 'approved_by', 'version'
])]
class LearningEnrollment extends LearningModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'decimal:2',
            'score' => 'decimal:2',
            'passed' => 'boolean',
            'enrolled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'due_date' => 'date',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $enrollment): void {
            $enrollment->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, LearningEnrollment> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningCourse, LearningEnrollment> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningCourseVersion, LearningEnrollment> */
    public function courseVersion(): BelongsTo
    {
        return $this->belongsTo(LearningCourseVersion::class, 'course_version_id');
    }

    /** @return BelongsTo<LearningProgram, LearningEnrollment> */
    public function program(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'program_id');
    }

    /** @return BelongsTo<LearningPath, LearningEnrollment> */
    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'path_id');
    }

    /** @return BelongsTo<LearningSession, LearningEnrollment> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id');
    }

    /** @return BelongsTo<User, LearningEnrollment> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<LearningProgress> */
    public function progressRecords(): HasMany
    {
        return $this->hasMany(LearningProgress::class, 'enrollment_id');
    }

    /** @return HasMany<LearningAssessmentAttempt> */
    public function attempts(): HasMany
    {
        return $this->hasMany(LearningAssessmentAttempt::class, 'enrollment_id');
    }
}
