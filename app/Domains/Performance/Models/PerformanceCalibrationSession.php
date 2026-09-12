<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceCalibrationSession extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'name',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class, 'cycle_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(PerformanceCalibrationRecord::class, 'session_id');
    }
}
