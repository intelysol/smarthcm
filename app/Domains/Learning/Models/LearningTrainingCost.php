<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'course_id', 'session_id', 'employee_id', 'provider_id', 'cost_type', 'amount', 'currency', 'description'])]
class LearningTrainingCost extends LearningModel
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<LearningCourse, LearningTrainingCost> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningSession, LearningTrainingCost> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id');
    }

    /** @return BelongsTo<Employee, LearningTrainingCost> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningProvider, LearningTrainingCost> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(LearningProvider::class, 'provider_id');
    }
}
