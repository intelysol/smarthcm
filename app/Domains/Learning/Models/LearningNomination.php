<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'course_id', 'employee_id', 'nominated_by', 'status', 'is_mandatory', 'reason', 'response_notes', 'responded_at'])]
class LearningNomination extends LearningModel
{
    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'responded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningNomination> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<Employee, LearningNomination> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<Employee, LearningNomination> */
    public function nominator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'nominated_by');
    }
}
