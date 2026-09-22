<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiProductionReadiness extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_production_readiness';

    protected $fillable = [
        'tenant_id',
        'use_case_code',
        'governance_score',
        'security_score',
        'quality_score',
        'grounding_score',
        'evaluation_score',
        'observability_score',
        'performance_score',
        'cost_score',
        'overall_readiness_score',
        'readiness_status',
        'blockers',
        'evaluated_at',
    ];

    protected $casts = [
        'governance_score' => 'decimal:2',
        'security_score' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'grounding_score' => 'decimal:2',
        'evaluation_score' => 'decimal:2',
        'observability_score' => 'decimal:2',
        'performance_score' => 'decimal:2',
        'cost_score' => 'decimal:2',
        'overall_readiness_score' => 'decimal:2',
        'blockers' => 'array',
        'evaluated_at' => 'datetime',
    ];
}
