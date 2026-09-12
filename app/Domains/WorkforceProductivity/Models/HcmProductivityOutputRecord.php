<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityOutputRecord extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_output_records';

    protected $fillable = [
        'tenant_id',
        'output_date',
        'metric_definition_id',
        'employee_id',
        'department_id',
        'cost_center_id',
        'location_id',
        'project_id',
        'shift_id',
        'output_type',
        'units_completed',
        'units_defective',
        'rework_count',
        'revenue_generated',
        'quality_score',
        'source_domain',
        'source_record_id',
        'nature',
        'metadata',
    ];

    protected $casts = [
        'output_date' => 'date',
        'units_completed' => 'decimal:4',
        'units_defective' => 'decimal:4',
        'rework_count' => 'decimal:4',
        'revenue_generated' => 'decimal:4',
        'quality_score' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function metricDefinition(): BelongsTo
    {
        return $this->belongsTo(HcmProductivityMetricDefinition::class, 'metric_definition_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
