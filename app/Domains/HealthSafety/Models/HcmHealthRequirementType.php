<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmHealthRequirementType extends Model
{
    use HasUuids;

    protected $table = 'hcm_health_requirement_types';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'default_renewal_required',
        'default_validity_months',
        'is_active',
    ];

    protected $casts = [
        'default_renewal_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(HcmHealthRequirement::class, 'health_requirement_type_id');
    }
}
