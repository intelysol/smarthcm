<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiInteractionTelemetry extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_interaction_telemetry';

    protected $fillable = [
        'tenant_id',
        'session_id',
        'message_id',
        'user_id',
        'employee_id',
        'use_case_code',
        'model_code',
        'provider',
        'prompt_version',
        'policy_version',
        'lifecycle_status',
        'failure_reason',
        'input_tokens',
        'output_tokens',
        'cached_tokens',
        'latency_ms',
        'cost_estimate',
        'tool_calls',
        'retrieval_citations',
        'grounding_score',
        'is_sensitive_redacted',
    ];

    protected $casts = [
        'tool_calls' => 'array',
        'retrieval_citations' => 'array',
        'is_sensitive_redacted' => 'boolean',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cached_tokens' => 'integer',
        'latency_ms' => 'integer',
        'cost_estimate' => 'decimal:6',
        'grounding_score' => 'decimal:2',
    ];

    public function feedback()
    {
        return $this->hasMany(HcmAiFeedback::class, 'telemetry_id');
    }
}
