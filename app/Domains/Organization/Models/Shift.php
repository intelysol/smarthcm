<?php

namespace App\Domains\Organization\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'shift_name', 'start_time', 'end_time', 'grace_period_minutes', 'break_rules', 'overtime_rules', 'weekly_off', 'is_night_shift', 'shift_type', 'status', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['break_rules' => 'array', 'overtime_rules' => 'array', 'weekly_off' => 'array', 'is_night_shift' => 'boolean', 'grace_period_minutes' => 'integer'];

    public function workLocations(): HasMany
    {
        return $this->hasMany(WorkLocation::class);
    }
}
