<?php

namespace App\Domains\Onboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOnboardingProvisioningRequest extends Model
{
    use HasUuids;

    protected $table = 'hcm_onboarding_provisioning_requests';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'request_type',
        'title',
        'specifications',
        'status',
        'assigned_to',
        'completed_at',
    ];

    protected $casts = [
        'specifications' => 'array',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(HcmOnboardingCase::class, 'case_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
