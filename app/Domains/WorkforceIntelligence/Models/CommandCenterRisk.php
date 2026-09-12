<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterRisk extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_risks';

    protected $fillable = [
        'tenant_id',
        'department_id',
        'risk_code',
        'title',
        'category',
        'severity',
        'impact_score',
        'likelihood_score',
        'exposure_value',
        'description',
        'status',
        'attribution_level',
        'causal_factors',
        'recommended_mitigation',
        'owner_user_id',
    ];

    protected $casts = [
        'impact_score' => 'decimal:2',
        'likelihood_score' => 'decimal:2',
        'exposure_value' => 'decimal:2',
        'causal_factors' => 'array',
    ];
}
