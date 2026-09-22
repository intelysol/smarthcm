<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiModelPerformance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_model_performance';

    protected $fillable = [
        'tenant_id',
        'model_code',
        'provider',
        'benchmark_version',
        'accuracy_pct',
        'grounding_pct',
        'safety_pct',
        'avg_latency_ms',
        'p95_latency_ms',
        'cost_per_1k_tokens',
        'availability_pct',
        'tool_success_pct',
        'benchmark_status',
    ];

    protected $casts = [
        'accuracy_pct' => 'decimal:2',
        'grounding_pct' => 'decimal:2',
        'safety_pct' => 'decimal:2',
        'avg_latency_ms' => 'integer',
        'p95_latency_ms' => 'integer',
        'cost_per_1k_tokens' => 'decimal:6',
        'availability_pct' => 'decimal:2',
        'tool_success_pct' => 'decimal:2',
    ];
}
