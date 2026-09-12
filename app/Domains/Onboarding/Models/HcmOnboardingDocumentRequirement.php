<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmOnboardingDocumentRequirement extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_document_requirements';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'document_type',
        'title',
        'is_mandatory',
        'status',
        'file_path',
        'file_name',
        'submitted_at',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingCase::class, 'case_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(HcmOnboardingDocumentReview::class, 'document_requirement_id');
    }
}
