<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOnboardingDocumentReview extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_document_reviews';

    protected $fillable = [
        'tenant_id',
        'document_requirement_id',
        'reviewer_id',
        'status',
        'comments',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function documentRequirement(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingDocumentRequirement::class, 'document_requirement_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
