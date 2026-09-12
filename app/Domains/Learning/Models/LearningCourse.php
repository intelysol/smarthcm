<?php

namespace App\Domains\Learning\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'code', 'title', 'description', 'short_description',
    'category_id', 'difficulty', 'delivery_type', 'duration', 'duration_unit',
    'language', 'status', 'visibility', 'provider_id', 'current_version',
    'passing_score', 'credit_points', 'max_attempts', 'requires_attendance',
    'min_attendance_percentage', 'created_by', 'updated_by'
])]
class LearningCourse extends LearningModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'duration' => 'decimal:2',
            'passing_score' => 'decimal:2',
            'credit_points' => 'decimal:2',
            'min_attendance_percentage' => 'decimal:2',
            'requires_attendance' => 'boolean',
            'current_version' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $course): void {
            $course->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<LearningCategory, LearningCourse> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(LearningCategory::class, 'category_id');
    }

    /** @return BelongsTo<LearningProvider, LearningCourse> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(LearningProvider::class, 'provider_id');
    }

    /** @return BelongsTo<User, LearningCourse> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<LearningCourseVersion> */
    public function versions(): HasMany
    {
        return $this->hasMany(LearningCourseVersion::class, 'course_id')->orderBy('version_number', 'desc');
    }

    /** @return HasMany<LearningCourseObjective> */
    public function objectives(): HasMany
    {
        return $this->hasMany(LearningCourseObjective::class, 'course_id')->orderBy('sort_order');
    }

    /** @return HasMany<LearningCoursePrerequisite> */
    public function prerequisites(): HasMany
    {
        return $this->hasMany(LearningCoursePrerequisite::class, 'course_id');
    }

    /** @return HasMany<LearningCourseModule> */
    public function modules(): HasMany
    {
        return $this->hasMany(LearningCourseModule::class, 'course_id')->orderBy('sort_order');
    }

    /** @return HasMany<LearningItem> */
    public function items(): HasMany
    {
        return $this->hasMany(LearningItem::class, 'course_id')->orderBy('sort_order');
    }

    /** @return HasMany<LearningAssessment> */
    public function assessments(): HasMany
    {
        return $this->hasMany(LearningAssessment::class, 'course_id');
    }

    /** @return HasMany<LearningSession> */
    public function sessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'course_id');
    }

    /** @return HasMany<LearningEnrollment> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(LearningEnrollment::class, 'course_id');
    }

    /** @return HasMany<LearningCertificate> */
    public function certificates(): HasMany
    {
        return $this->hasMany(LearningCertificate::class, 'course_id');
    }

    /** @return HasMany<LearningFeedback> */
    public function feedback(): HasMany
    {
        return $this->hasMany(LearningFeedback::class, 'course_id');
    }

    /** @return HasMany<LearningCourseEvaluation> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(LearningCourseEvaluation::class, 'course_id');
    }
}
