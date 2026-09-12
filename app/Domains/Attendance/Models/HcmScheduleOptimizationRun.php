<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmScheduleOptimizationRun extends Model
{
    use HasUuids;

    protected $table = 'hcm_schedule_optimization_runs';

    protected $fillable = [
        'tenant_id',
        'roster_period_id',
        'strategy',
        'status',
        'metrics_before',
        'metrics_after',
        'proposed_assignments',
        'quality_score',
        'ran_by',
        'error_message',
    ];

    protected $casts = [
        'metrics_before' => 'array',
        'metrics_after' => 'array',
        'proposed_assignments' => 'array',
        'quality_score' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rosterPeriod(): BelongsTo
    {
        return $this->belongsTo(RosterPeriod::class, 'roster_period_id');
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ran_by');
    }
}
