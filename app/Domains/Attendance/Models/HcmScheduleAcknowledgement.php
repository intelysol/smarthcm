<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmScheduleAcknowledgement extends Model
{
    use HasUuids;

    protected $table = 'hcm_schedule_acknowledgements';

    protected $fillable = [
        'tenant_id',
        'roster_period_id',
        'employee_id',
        'status',
        'dispute_reason',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rosterPeriod(): BelongsTo
    {
        return $this->belongsTo(RosterPeriod::class, 'roster_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
