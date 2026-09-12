<?php

namespace App\Domains\Organization\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'holiday_calendar_id', 'name', 'holiday_date', 'is_recurring', 'holiday_type', 'group_name', 'description', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['holiday_date' => 'date', 'is_recurring' => 'boolean'];

    public function holidayCalendar(): BelongsTo
    {
        return $this->belongsTo(HolidayCalendar::class);
    }
}
