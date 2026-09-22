<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiBudgetPolicy extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_budget_policies';

    protected $fillable = [
        'tenant_id',
        'scope',
        'target_identifier',
        'budget_period',
        'budget_limit_usd',
        'current_spend_usd',
        'threshold_alert_pct',
        'enforcement_action',
        'is_active',
    ];

    protected $casts = [
        'budget_limit_usd' => 'decimal:2',
        'current_spend_usd' => 'decimal:2',
        'threshold_alert_pct' => 'integer',
        'is_active' => 'boolean',
    ];
}
