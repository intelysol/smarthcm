<?php

namespace App\Domains\Attendance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftPatternDay extends Model
{
    use HasUuids;

    protected $table = 'shift_pattern_days';

    protected $fillable = [
        'tenant_id',
        'shift_pattern_id',
        'day_sequence',
        'shift_definition_id',
        'is_off_day',
        'notes',
    ];

    protected $casts = [
        'day_sequence' => 'integer',
        'is_off_day' => 'boolean',
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(ShiftPattern::class, 'shift_pattern_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class, 'shift_definition_id');
    }
}
