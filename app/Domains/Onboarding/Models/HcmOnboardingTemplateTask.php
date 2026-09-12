<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOnboardingTemplateTask extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_template_tasks';

    protected $fillable = [
        'tenant_id',
        'template_version_id',
        'title',
        'description',
        'task_type',
        'owner_role',
        'due_offset_days',
        'sla_hours',
        'is_required',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'due_offset_days' => 'integer',
        'sla_hours' => 'integer',
        'is_required' => 'boolean',
        'display_order' => 'integer',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingTemplateVersion::class, 'template_version_id');
    }
}
