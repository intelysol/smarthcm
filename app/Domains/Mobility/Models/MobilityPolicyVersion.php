<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobilityPolicyVersion extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_policy_versions';

    protected $fillable = [
        'tenant_id',
        'program_id',
        'version_number',
        'effective_from',
        'effective_to',
        'housing_policy_type',
        'housing_monthly_cap',
        'relocation_allowance',
        'mobility_premium_percentage',
        'hardship_allowance_percentage',
        'tax_equalization_enabled',
        'education_allowance_enabled',
        'education_allowance_per_child',
        'rules_configuration',
        'is_active',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'housing_monthly_cap' => 'decimal:4',
        'relocation_allowance' => 'decimal:4',
        'mobility_premium_percentage' => 'decimal:2',
        'hardship_allowance_percentage' => 'decimal:2',
        'tax_equalization_enabled' => 'boolean',
        'education_allowance_enabled' => 'boolean',
        'education_allowance_per_child' => 'decimal:4',
        'rules_configuration' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(MobilityProgram::class, 'program_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(MobilityPolicyRule::class, 'policy_version_id');
    }
}
