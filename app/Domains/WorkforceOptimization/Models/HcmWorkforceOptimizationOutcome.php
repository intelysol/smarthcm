<?php

namespace App\Domains\WorkforceOptimization\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class HcmWorkforceOptimizationOutcome extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_workforce_optimization_outcomes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'recommendation_id',
        'action_id',
        'measurement_date',
        'metric_name',
        'pre_action_value',
        'post_action_value',
        'variance_value',
        'variance_pct',
        'realized_financial_impact',
        'causality_label',
        'notes',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'pre_action_value' => 'decimal:4',
        'post_action_value' => 'decimal:4',
        'variance_value' => 'decimal:4',
        'variance_pct' => 'decimal:2',
        'realized_financial_impact' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationRecommendation::class, 'recommendation_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationAction::class, 'action_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
