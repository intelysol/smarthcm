<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterHealthIndex extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_health_indices';

    protected $fillable = [
        'tenant_id',
        'department_id',
        'period_key',
        'composite_score',
        'health_band',
        'capacity_dimension_score',
        'productivity_dimension_score',
        'cost_dimension_score',
        'retention_dimension_score',
        'skills_dimension_score',
        'compliance_dimension_score',
        'formula_weights',
        'confidence_score',
        'summary_diagnosis',
        'contributing_factors',
        'evaluated_at',
    ];

    protected $casts = [
        'composite_score' => 'decimal:2',
        'capacity_dimension_score' => 'decimal:2',
        'productivity_dimension_score' => 'decimal:2',
        'cost_dimension_score' => 'decimal:2',
        'retention_dimension_score' => 'decimal:2',
        'skills_dimension_score' => 'decimal:2',
        'compliance_dimension_score' => 'decimal:2',
        'formula_weights' => 'array',
        'confidence_score' => 'decimal:2',
        'contributing_factors' => 'array',
        'evaluated_at' => 'datetime',
    ];
}
