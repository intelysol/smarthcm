<?php

namespace App\Domains\Attendance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkCalendarDay extends Model
{
    use HasUuids;

    protected $table = 'work_calendar_days';

    protected $fillable = [
        'tenant_id',
        'work_calendar_id',
        'day_of_week',
        'is_working_day',
        'standard_hours_minutes',
        'notes',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_working_day' => 'boolean',
        'standard_hours_minutes' => 'integer',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(WorkCalendar::class, 'work_calendar_id');
    }
}
