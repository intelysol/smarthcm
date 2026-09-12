<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsurancePolicy extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'insurance_policies';

    protected $fillable = [
        'tenant_id',
        'benefit_provider_id',
        'policy_number',
        'policy_name',
        'insurance_type',
        'start_date',
        'end_date',
        'total_premium',
        'max_aggregate_limit',
        'policy_terms',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_premium' => 'decimal:4',
        'max_aggregate_limit' => 'decimal:4',
        'policy_terms' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(BenefitProvider::class, 'benefit_provider_id');
    }

    public function coverages(): HasMany
    {
        return $this->hasMany(InsuranceCoverage::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }
}
