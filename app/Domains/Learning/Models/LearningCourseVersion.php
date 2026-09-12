<?php

namespace App\Domains\Learning\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id', 'course_id', 'version_number', 'title', 'description',
    'content_snapshot', 'change_log', 'status', 'published_at', 'created_by'
])]
class LearningCourseVersion extends LearningModel
{
    protected function casts(): array
    {
        return [
            'content_snapshot' => 'array',
            'version_number' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningCourseVersion> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<User, LearningCourseVersion> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<LearningCourseModule> */
    public function modules(): HasMany
    {
        return $this->hasMany(LearningCourseModule::class, 'course_version_id');
    }
}
