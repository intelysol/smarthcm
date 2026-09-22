<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantOnboardingWizard extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_onboarding_wizards';

    protected $fillable = [
        'tenant_id',
        'status',
        'current_step',
        'progress_pct',
        'step_data',
        'completed_steps',
        'completed_at',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'progress_pct' => 'integer',
        'step_data' => 'array',
        'completed_steps' => 'array',
        'completed_at' => 'datetime',
    ];
}
