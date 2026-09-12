<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityRoiCalculation extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_roi_calculations';

    protected $fillable = [
        'tenant_id',
        'roi_model_id',
        'department_id',
        'investment_name',
        'investment_cost',
        'operational_benefit',
        'net_benefit',
        'roi_percentage',
        'payback_period_months',
        'causality_label',
        'pre_investment_output_rate',
        'post_investment_output_rate',
        'calculation_details',
    ];

    protected $casts = [
        'investment_cost' => 'decimal:4',
        'operational_benefit' => 'decimal:4',
        'net_benefit' => 'decimal:4',
        'roi_percentage' => 'decimal:2',
        'payback_period_months' => 'decimal:2',
        'pre_investment_output_rate' => 'decimal:4',
        'post_investment_output_rate' => 'decimal:4',
        'calculation_details' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(HcmProductivityRoiModel::class, 'roi_model_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
