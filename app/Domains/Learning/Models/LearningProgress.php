<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'employee_id', 'enrollment_id', 'learning_item_id',
    'status', 'progress_percentage', 'time_spent_seconds',
    'video_watched_percentage', 'started_at', 'completed_at', 'last_accessed_at'
])]
class LearningProgress extends LearningModel
{
    protected $table = 'learning_progress';

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'decimal:2',
            'video_watched_percentage' => 'decimal:2',
            'time_spent_seconds' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Employee, LearningProgress> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningEnrollment, LearningProgress> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(LearningEnrollment::class, 'enrollment_id');
    }

    /** @return BelongsTo<LearningItem, LearningProgress> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(LearningItem::class, 'learning_item_id');
    }
}
