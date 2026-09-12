<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceOptimizationRecommendationFactor extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_recommendation_factors';

    protected $fillable = [
        'tenant_id',
        'recommendation_id',
        'factor_type',
        'name',
        'score',
        'weight',
        'details',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationRecommendation::class, 'recommendation_id');
    }
}
