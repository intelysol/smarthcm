<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivitySnapshot extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_snapshots';

    protected $fillable = [
        'tenant_id',
        'snapshot_number',
        'period_type',
        'period_name',
        'period_start',
        'period_end',
        'version',
        'status',
        'total_output',
        'total_labor_hours',
        'total_productive_hours',
        'total_labor_cost',
        'average_productivity_rate',
        'average_utilization_rate',
        'average_cost_per_unit',
        'total_fte',
        'headcount',
        'idempotency_key',
        'locked_at',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'version' => 'integer',
        'total_output' => 'decimal:4',
        'total_labor_hours' => 'decimal:2',
        'total_productive_hours' => 'decimal:2',
        'total_labor_cost' => 'decimal:4',
        'average_productivity_rate' => 'decimal:4',
        'average_utilization_rate' => 'decimal:2',
        'average_cost_per_unit' => 'decimal:4',
        'total_fte' => 'decimal:2',
        'headcount' => 'integer',
        'locked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
