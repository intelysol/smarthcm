<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'session_id', 'employee_id', 'performance_rating', 'potential_rating',
    'nine_box_position', 'readiness_level', 'retention_risk', 'vacancy_risk',
    'mobility_rating', 'development_priority', 'manager_notes', 'calibration_notes',
    'override_reason', 'is_overridden'
])]
class TalentReviewRecord extends CareerModel
{
    protected function casts(): array
    {
        return [
            'performance_rating' => 'decimal:2',
            'potential_rating' => 'decimal:2',
            'is_overridden' => 'boolean',
        ];
    }

    /** @return BelongsTo<TalentReviewSession, TalentReviewRecord> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(TalentReviewSession::class, 'session_id');
    }

    /** @return BelongsTo<Employee, TalentReviewRecord> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
