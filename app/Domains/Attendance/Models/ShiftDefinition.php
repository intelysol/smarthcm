<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftDefinition extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'shift_definitions';

    protected $fillable = [
        'tenant_id',
        'shift_code',
        'name',
        'description',
        'shift_type',
        'start_time',
        'end_time',
        'timezone',
        'duration_minutes',
        'core_start_time',
        'core_end_time',
        'required_daily_minutes',
        'split_second_start',
        'split_second_end',
        'grace_period_minutes',
        'late_threshold_minutes',
        'early_departure_threshold_minutes',
        'overtime_eligible',
        'min_overtime_threshold_minutes',
        'rounding_rule_minutes',
        'is_night_shift',
        'is_flexible',
        'is_split',
        'is_active',
        'color_code',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'required_daily_minutes' => 'integer',
        'grace_period_minutes' => 'integer',
        'late_threshold_minutes' => 'integer',
        'early_departure_threshold_minutes' => 'integer',
        'overtime_eligible' => 'boolean',
        'min_overtime_threshold_minutes' => 'integer',
        'rounding_rule_minutes' => 'integer',
        'is_night_shift' => 'boolean',
        'is_flexible' => 'boolean',
        'is_split' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(ShiftBreak::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
