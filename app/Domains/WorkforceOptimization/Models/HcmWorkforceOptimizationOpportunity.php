<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceOptimizationOpportunity extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_opportunities';

    protected $fillable = [
        'tenant_id',
        'run_id',
        'opportunity_code',
        'title',
        'category',
        'severity',
        'department_id',
        'location_id',
        'role_or_skill',
        'estimated_impact_amount',
        'currency',
        'capacity_gap_hours',
        'confidence_score',
        'details',
        'status',
    ];

    protected $casts = [
        'estimated_impact_amount' => 'decimal:4',
        'capacity_gap_hours' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'details' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationRun::class, 'run_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationRecommendation::class, 'opportunity_id');
    }
}
