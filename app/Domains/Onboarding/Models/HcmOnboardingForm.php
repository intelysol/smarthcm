<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmOnboardingForm extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_forms';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'schema_definition',
        'is_active',
    ];

    protected $casts = [
        'schema_definition' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HcmOnboardingFormSubmission::class, 'form_id');
    }
}
