<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmOnboardingTemplate extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_onboarding_templates';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'department_id',
        'location_id',
        'employment_type',
        'worker_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HcmOnboardingTemplateVersion::class, 'template_id');
    }

    public function latestVersion(): ?HcmOnboardingTemplateVersion
    {
        return $this->versions()->latest('version_number')->first();
    }
}
