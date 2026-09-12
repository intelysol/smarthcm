<?php

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmComplianceRequirementType extends Model
{
    use HasUuids;

    protected $table = 'hcm_compliance_requirement_types';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'default_renewal_required',
        'default_grace_period_days',
        'is_active',
    ];

    protected $casts = [
        'default_renewal_required' => 'boolean',
        'default_grace_period_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(HcmComplianceRequirement::class, 'requirement_type_id');
    }
}
