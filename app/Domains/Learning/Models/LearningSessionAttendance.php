<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'session_id', 'employee_id', 'status', 'check_in', 'check_out', 'attendance_minutes', 'notes'])]
class LearningSessionAttendance extends LearningModel
{
    protected $table = 'learning_session_attendance';

    protected function casts(): array
    {
        return [
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'attendance_minutes' => 'integer',
        ];
    }

    /** @return BelongsTo<LearningSession, LearningSessionAttendance> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id');
    }

    /** @return BelongsTo<Employee, LearningSessionAttendance> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
