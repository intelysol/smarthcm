<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityForecast extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_forecasts';

    protected $fillable = [
        'tenant_id',
        'forecast_number',
        'department_id',
        'forecast_start',
        'forecast_end',
        'projected_output',
        'projected_labor_hours',
        'projected_productive_hours',
        'projected_utilization_rate',
        'projected_labor_cost',
        'projected_cost_per_unit',
        'assumptions',
    ];

    protected $casts = [
        'forecast_start' => 'date',
        'forecast_end' => 'date',
        'projected_output' => 'decimal:4',
        'projected_labor_hours' => 'decimal:2',
        'projected_productive_hours' => 'decimal:2',
        'projected_utilization_rate' => 'decimal:2',
        'projected_labor_cost' => 'decimal:4',
        'projected_cost_per_unit' => 'decimal:4',
        'assumptions' => 'array',
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
