<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id', 'course_id', 'course_version_id', 'provider_id',
    'instructor_id', 'venue_id', 'title', 'start_datetime', 'end_datetime',
    'capacity', 'enrollment_deadline', 'status'
])]
class LearningSession extends LearningModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'enrollment_deadline' => 'datetime',
            'capacity' => 'integer',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningSession> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningCourseVersion, LearningSession> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(LearningCourseVersion::class, 'course_version_id');
    }

    /** @return BelongsTo<LearningProvider, LearningSession> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(LearningProvider::class, 'provider_id');
    }

    /** @return BelongsTo<LearningInstructor, LearningSession> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(LearningInstructor::class, 'instructor_id');
    }

    /** @return BelongsTo<LearningVenue, LearningSession> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(LearningVenue::class, 'venue_id');
    }

    /** @return HasMany<LearningEnrollment> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(LearningEnrollment::class, 'session_id');
    }

    /** @return HasMany<LearningSessionAttendance> */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(LearningSessionAttendance::class, 'session_id');
    }

    /** @return HasMany<LearningWaitlist> */
    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(LearningWaitlist::class, 'session_id')->orderBy('position');
    }
}
