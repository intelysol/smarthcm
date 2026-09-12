<?php

namespace App\Domains\Attendance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkCalendarException extends Model
{
    use HasUuids;

    protected $table = 'work_calendar_exceptions';

    protected $fillable = [
        'tenant_id',
        'work_calendar_id',
        'exception_date',
        'is_working_day',
        'custom_hours_minutes',
        'reason',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'is_working_day' => 'boolean',
        'custom_hours_minutes' => 'integer',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(WorkCalendar::class, 'work_calendar_id');
    }
}
