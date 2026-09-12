<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceOptimizationRecommendation extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_recommendations';

    protected $fillable = [
        'tenant_id',
        'opportunity_id',
        'recommendation_code',
        'action_type',
        'title',
        'executive_summary',
        'source_department_id',
        'target_department_id',
        'candidate_employee_id',
        'decision_score',
        'cost_impact',
        'capacity_impact_hours',
        'productivity_impact_pct',
        'time_to_realize_days',
        'risk_level',
        'confidence',
        'lifecycle_status',
        'authoritative_module',
        'required_approval_role',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'decision_score' => 'decimal:2',
        'cost_impact' => 'decimal:4',
        'capacity_impact_hours' => 'decimal:2',
        'productivity_impact_pct' => 'decimal:2',
        'time_to_realize_days' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationOpportunity::class, 'opportunity_id');
    }

    public function sourceDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'source_department_id');
    }

    public function targetDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'target_department_id');
    }

    public function candidateEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'candidate_employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function factors(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationRecommendationFactor::class, 'recommendation_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationAction::class, 'recommendation_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationFeedback::class, 'recommendation_id');
    }
}
