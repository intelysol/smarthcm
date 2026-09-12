<?php

namespace App\Domains\WorkforceOptimization\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmWorkforceOptimizationAction extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_optimization_actions';

    protected $fillable = [
        'tenant_id',
        'recommendation_id',
        'action_number',
        'title',
        'target_module',
        'payload',
        'status',
        'dispatched_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'dispatched_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforceOptimizationRecommendation::class, 'recommendation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(HcmWorkforceOptimizationOutcome::class, 'action_id');
    }
}
