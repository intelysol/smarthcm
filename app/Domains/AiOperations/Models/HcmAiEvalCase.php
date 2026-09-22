<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiEvalCase extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_eval_cases';

    protected $fillable = [
        'tenant_id',
        'dataset_id',
        'case_code',
        'use_case_code',
        'prompt_input',
        'expected_behavior',
        'expected_tools',
        'expected_sources',
        'expected_policy',
        'risk_level',
        'rubric_criteria',
    ];

    protected $casts = [
        'expected_tools' => 'array',
        'expected_sources' => 'array',
        'rubric_criteria' => 'array',
    ];

    public function dataset()
    {
        return $this->belongsTo(HcmAiEvalDataset::class, 'dataset_id');
    }
}
