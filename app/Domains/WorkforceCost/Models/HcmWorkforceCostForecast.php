<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCostForecast extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_forecasts';

    protected $fillable = [
        'tenant_id',
        'department_id',
        'cost_center_id',
        'forecast_period_start',
        'forecast_period_end',
        'baseline_amount',
        'headcount_impact_amount',
        'salary_increase_impact_amount',
        'overtime_impact_amount',
        'contractor_impact_amount',
        'forecast_total_cost',
        'currency',
        'assumptions',
    ];

    protected $casts = [
        'forecast_period_start' => 'date',
        'forecast_period_end' => 'date',
        'baseline_amount' => 'decimal:4',
        'headcount_impact_amount' => 'decimal:4',
        'salary_increase_impact_amount' => 'decimal:4',
        'overtime_impact_amount' => 'decimal:4',
        'contractor_impact_amount' => 'decimal:4',
        'forecast_total_cost' => 'decimal:4',
        'assumptions' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}