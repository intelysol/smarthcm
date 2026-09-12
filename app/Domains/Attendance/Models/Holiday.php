<?php

namespace App\Domains\Attendance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasUuids;

    protected $table = 'holidays';

    protected $fillable = [
        'tenant_id',
        'holiday_calendar_id',
        'name',
        'holiday_date',
        'is_recurring',
        'is_optional',
        'holiday_type',
        'description',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_recurring' => 'boolean',
        'is_optional' => 'boolean',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(HolidayCalendar::class, 'holiday_calendar_id');
    }
}
