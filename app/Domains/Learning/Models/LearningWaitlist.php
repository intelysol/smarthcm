<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'session_id', 'employee_id', 'status', 'position', 'offered_at', 'expires_at'])]
class LearningWaitlist extends LearningModel
{
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'offered_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<LearningSession, LearningWaitlist> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id');
    }

    /** @return BelongsTo<Employee, LearningWaitlist> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
