<?php

namespace App\Domains\WorkforceOptimization\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class HcmWorkforceOptimizationFeedback extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_workforce_optimization_feedback';
    protected $keyType = 'string';
    public $incrementing = false;
    const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'recommendation_id',
        'user_id',
        'feedback_type',
        'rejection_reason_code',
        'rejection_narrative',
        'feasibility_score',
        'practicality_score',
        'business_context_adjustments',
        'created_at',
    ];

    protected $casts = [
        'feasibility_score' => 'integer',
        'practicality_score' => 'integer',
        'business_context_adjustments' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationRecommendation::class, 'recommendation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
