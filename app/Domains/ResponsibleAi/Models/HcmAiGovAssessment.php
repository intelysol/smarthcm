<?php

namespace App\Domains\ResponsibleAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiGovAssessment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_gov_assessments';

    protected $fillable = [
        'tenant_id',
        'use_case_id',
        'assessment_code',
        'assessor_name',
        'privacy_risk_score',
        'bias_risk_score',
        'security_risk_score',
        'decision_impact_score',
        'human_oversight_mechanism',
        'contestability_remediation',
        'recommendation',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'privacy_risk_score' => 'decimal:2',
        'bias_risk_score' => 'decimal:2',
        'security_risk_score' => 'decimal:2',
        'decision_impact_score' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function useCase()
    {
        return $this->belongsTo(HcmAiGovUseCase::class, 'use_case_id');
    }
}
