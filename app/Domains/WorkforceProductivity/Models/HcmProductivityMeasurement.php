<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmProductivityMeasurement extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_measurements';

    protected $fillable = [
        'tenant_id',
        'metric_definition_id',
        'metric_version_id',
        'department_id',
        'location_id',
        'shift_id',
        'employee_id',
        'period_type',
        'period_name',
        'period_start',
        'period_end',
        'output_volume',
        'labor_hours',
        'productive_hours',
        'scheduled_hours',
        'available_hours',
        'overtime_hours',
        'idle_hours',
        'productivity_rate',
        'utilization_rate',
        'quality_rate',
        'labor_cost',
        'cost_per_unit',
        'cost_per_productive_hour',
        'output_per_dollar',
        'data_quality_status',
        'source_provenance',
        'idempotency_key',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'output_volume' => 'decimal:4',
        'labor_hours' => 'decimal:2',
        'productive_hours' => 'decimal:2',
        'scheduled_hours' => 'decimal:2',
        'available_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'idle_hours' => 'decimal:2',
        'productivity_rate' => 'decimal:4',
        'utilization_rate' => 'decimal:2',
        'quality_rate' => 'decimal:2',
        'labor_cost' => 'decimal:4',
        'cost_per_unit' => 'decimal:4',
        'cost_per_productive_hour' => 'decimal:4',
        'output_per_dollar' => 'decimal:4',
        'source_provenance' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function metricDefinition(): BelongsTo
    {
        return $this->belongsTo(HcmProductivityMetricDefinition::class, 'metric_definition_id');
    }

    public function metricVersion(): BelongsTo
    {
        return $this->belongsTo(HcmProductivityMetricVersion::class, 'metric_version_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(HcmProductivityMeasurementLine::class, 'measurement_id');
    }
}
