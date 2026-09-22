<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiRegression extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_regressions';

    protected $fillable = [
        'tenant_id',
        'eval_run_id',
        'regression_code',
        'use_case_code',
        'model_code',
        'metric_name',
        'baseline_value',
        'current_value',
        'variance_pct',
        'status',
        'incident_id',
        'mitigation_notes',
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'variance_pct' => 'decimal:2',
    ];

    public function run()
    {
        return $this->belongsTo(HcmAiEvalRun::class, 'eval_run_id');
    }
}
