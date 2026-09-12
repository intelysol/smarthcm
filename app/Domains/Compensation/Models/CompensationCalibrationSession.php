<?php

namespace App\Domains\Compensation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompensationCalibrationSession extends CompensationModel
{
    protected $fillable = [
        'tenant_id',
        'compensation_cycle_id',
        'title',
        'scope_type',
        'scope_id',
        'status',
        'session_date',
        'facilitators',
        'budget_metrics',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'datetime',
            'facilitators' => 'array',
            'budget_metrics' => 'array',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(CompensationCycle::class, 'compensation_cycle_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(CompensationCalibrationRecord::class, 'compensation_calibration_session_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
