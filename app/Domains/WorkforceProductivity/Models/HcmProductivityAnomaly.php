<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityAnomaly extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_anomalies';

    protected $fillable = [
        'tenant_id',
        'anomaly_type',
        'severity',
        'department_id',
        'location_id',
        'shift_id',
        'detected_date',
        'observed_value',
        'expected_baseline',
        'variance_pct',
        'possible_drivers',
        'status',
    ];

    protected $casts = [
        'detected_date' => 'date',
        'observed_value' => 'decimal:4',
        'expected_baseline' => 'decimal:4',
        'variance_pct' => 'decimal:2',
        'possible_drivers' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
