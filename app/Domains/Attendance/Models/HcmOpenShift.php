<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\WorkLocation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmOpenShift extends Model
{
    use HasUuids;

    protected $table = 'hcm_open_shifts';

    protected $fillable = [
        'tenant_id',
        'roster_period_id',
        'roster_date',
        'shift_definition_id',
        'department_id',
        'location_id',
        'required_skill_id',
        'slots_total',
        'slots_filled',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'roster_date' => 'date',
        'slots_total' => 'integer',
        'slots_filled' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rosterPeriod(): BelongsTo
    {
        return $this->belongsTo(RosterPeriod::class, 'roster_period_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'shift_definition_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'location_id');
    }

    public function requiredSkill(): BelongsTo
    {
        return $this->belongsTo(CareerSkill::class, 'required_skill_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(HcmOpenShiftBid::class, 'open_shift_id');
    }
}
