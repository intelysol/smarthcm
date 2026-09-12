<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOnboardingProbationReview extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_probation_reviews';

    protected $fillable = [
        'tenant_id',
        'probation_id',
        'reviewer_id',
        'performance_rating',
        'recommendation',
        'comments',
        'reviewed_at',
    ];

    protected $casts = [
        'performance_rating' => 'decimal:1',
        'reviewed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function probation(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingProbation::class, 'probation_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
