<?php

namespace App\Domains\ResponsibleAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiGovFairnessCheck extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_gov_fairness_checks';

    protected $fillable = [
        'tenant_id',
        'use_case_id',
        'check_code',
        'metric_evaluated',
        'variance_percentage',
        'threshold_allowed',
        'fairness_status',
        'aggregate_data_sample',
        'investigation_notes',
        'evaluated_at',
    ];

    protected $casts = [
        'variance_percentage' => 'decimal:2',
        'threshold_allowed' => 'decimal:2',
        'aggregate_data_sample' => 'array',
        'evaluated_at' => 'datetime',
    ];
}
