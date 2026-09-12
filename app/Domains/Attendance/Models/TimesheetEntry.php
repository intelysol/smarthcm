<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimesheetEntry extends Model
{
    use HasUuids;

    protected $table = 'timesheet_entries';

    protected $fillable = [
        'tenant_id',
        'timesheet_id',
        'attendance_session_id',
        'entry_date',
        'shift_code',
        'scheduled_minutes',
        'worked_minutes',
        'regular_minutes',
        'overtime_minutes',
        'approved_overtime_minutes',
        'late_minutes',
        'early_minutes',
        'absence_minutes',
        'is_holiday',
        'is_weekend',
        'is_leave',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'scheduled_minutes' => 'integer',
        'worked_minutes' => 'integer',
        'regular_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'approved_overtime_minutes' => 'integer',
        'late_minutes' => 'integer',
        'early_minutes' => 'integer',
        'absence_minutes' => 'integer',
        'is_holiday' => 'boolean',
        'is_weekend' => 'boolean',
        'is_leave' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function timeAllocations(): HasMany
    {
        return $this->hasMany(HcmTimeAllocation::class, 'timesheet_entry_id');
    }
}
