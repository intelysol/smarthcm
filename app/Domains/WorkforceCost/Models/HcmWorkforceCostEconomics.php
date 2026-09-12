<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCostEconomics extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_economics';

    protected $fillable = [
        'tenant_id',
        'department_id',
        'period_start',
        'period_end',
        'cost_per_fte',
        'cost_per_employee',
        'cost_per_labor_hour',
        'cost_per_productive_hour',
        'overtime_cost_ratio',
        'contractor_ratio',
        'absence_cost_total',
        'vacancy_cost_total',
        'cost_per_available_capacity_hour',
        'cost_per_required_capacity_hour',
        'currency',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'cost_per_fte' => 'decimal:2',
        'cost_per_employee' => 'decimal:2',
        'cost_per_labor_hour' => 'decimal:4',
        'cost_per_productive_hour' => 'decimal:4',
        'overtime_cost_ratio' => 'decimal:2',
        'contractor_ratio' => 'decimal:2',
        'absence_cost_total' => 'decimal:4',
        'vacancy_cost_total' => 'decimal:4',
        'cost_per_available_capacity_hour' => 'decimal:4',
        'cost_per_required_capacity_hour' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}