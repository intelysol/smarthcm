<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiEvalRun extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_eval_runs';

    protected $fillable = [
        'tenant_id',
        'dataset_id',
        'run_code',
        'model_code',
        'provider',
        'prompt_version',
        'status',
        'accuracy_score',
        'grounding_score',
        'citation_score',
        'tool_correctness_score',
        'policy_compliance_score',
        'safety_score',
        'overall_quality_score',
        'total_cases_evaluated',
        'passed_cases',
        'failed_cases',
        'avg_latency_ms',
        'total_cost',
        'executed_by_user_id',
        'completed_at',
    ];

    protected $casts = [
        'accuracy_score' => 'decimal:2',
        'grounding_score' => 'decimal:2',
        'citation_score' => 'decimal:2',
        'tool_correctness_score' => 'decimal:2',
        'policy_compliance_score' => 'decimal:2',
        'safety_score' => 'decimal:2',
        'overall_quality_score' => 'decimal:2',
        'total_cases_evaluated' => 'integer',
        'passed_cases' => 'integer',
        'failed_cases' => 'integer',
        'avg_latency_ms' => 'integer',
        'total_cost' => 'decimal:4',
        'completed_at' => 'datetime',
    ];

    public function dataset()
    {
        return $this->belongsTo(HcmAiEvalDataset::class, 'dataset_id');
    }

    public function regressions()
    {
        return $this->hasMany(HcmAiRegression::class, 'eval_run_id');
    }
}
