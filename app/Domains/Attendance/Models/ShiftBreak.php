<?php

namespace App\Domains\Attendance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftBreak extends Model
{
    use HasUuids;

    protected $table = 'shift_breaks';

    protected $fillable = [
        'tenant_id',
        'shift_definition_id',
        'break_name',
        'break_type',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_automatic_deduction',
        'minimum_break_minutes',
        'maximum_break_minutes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'is_automatic_deduction' => 'boolean',
        'minimum_break_minutes' => 'integer',
        'maximum_break_minutes' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'shift_definition_id');
    }
}
