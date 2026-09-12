<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitProvider extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_providers';

    protected $fillable = [
        'tenant_id',
        'provider_code',
        'name',
        'provider_type',
        'contract_number',
        'policy_number',
        'contact_person',
        'contact_email',
        'contact_phone',
        'website',
        'contract_start_date',
        'contract_expiry_date',
        'integration_configuration',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'contract_start_date' => 'date',
        'contract_expiry_date' => 'date',
        'integration_configuration' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(BenefitPlan::class);
    }

    public function insurancePolicies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }
}
