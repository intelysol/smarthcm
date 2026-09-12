<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSessionBreak extends Model
{
    use HasUuids;

    protected $table = 'attendance_session_breaks';

    protected $fillable = [
        'tenant_id',
        'attendance_session_id',
        'shift_break_id',
        'break_start_time',
        'break_end_time',
        'duration_minutes',
        'is_paid',
        'is_exceeded',
        'excess_minutes',
    ];

    protected $casts = [
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'is_paid' => 'boolean',
        'is_exceeded' => 'boolean',
        'excess_minutes' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function shiftBreak(): BelongsTo
    {
        return $this->belongsTo(ShiftBreak::class, 'shift_break_id');
    }
}
