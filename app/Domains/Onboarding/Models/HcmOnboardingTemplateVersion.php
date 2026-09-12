<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmOnboardingTemplateVersion extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_template_versions';

    protected $fillable = [
        'tenant_id',
        'template_id',
        'version_number',
        'status',
        'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(HcmOnboardingTemplateTask::class, 'template_version_id')->orderBy('display_order');
    }

    public function cases(): HasMany
    {
        return $this->hasMany(HcmOnboardingCase::class, 'template_version_id');
    }
}
