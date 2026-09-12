<?php

namespace App\Domains\ResponsibleAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmAiGovUseCase extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'hcm_ai_gov_use_cases';

    protected $fillable = [
        'tenant_id',
        'use_case_code',
        'name',
        'description',
        'domain',
        'business_owner',
        'technical_owner',
        'status',
        'risk_level',
        'human_oversight',
        'affected_populations',
        'model_dependencies',
        'tool_dependencies',
        'approved_at',
        'approved_by_user_id',
    ];

    protected $casts = [
        'affected_populations' => 'array',
        'model_dependencies' => 'array',
        'tool_dependencies' => 'array',
        'approved_at' => 'datetime',
    ];

    public function assessments()
    {
        return $this->hasMany(HcmAiGovAssessment::class, 'use_case_id');
    }
}
