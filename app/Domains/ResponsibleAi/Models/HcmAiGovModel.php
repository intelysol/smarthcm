<?php

namespace App\Domains\ResponsibleAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiGovModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_gov_models';

    protected $fillable = [
        'tenant_id',
        'model_code',
        'provider',
        'model_name',
        'version',
        'model_type',
        'status',
        'risk_tier',
        'capabilities',
        'limitations',
        'data_restrictions',
        'cost_per_1k_tokens',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'data_restrictions' => 'array',
        'cost_per_1k_tokens' => 'decimal:6',
        'approved_at' => 'datetime',
    ];
}
