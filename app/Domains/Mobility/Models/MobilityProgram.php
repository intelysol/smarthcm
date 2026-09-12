<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobilityProgram extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_mobility_programs';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'mobility_type',
        'description',
        'min_duration_months',
        'max_duration_months',
        'requires_relocation',
        'requires_compliance_check',
        'is_active',
    ];

    protected $casts = [
        'min_duration_months' => 'integer',
        'max_duration_months' => 'integer',
        'requires_relocation' => 'boolean',
        'requires_compliance_check' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function policyVersions(): HasMany
    {
        return $this->hasMany(MobilityPolicyVersion::class, 'program_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(MobilityRequest::class, 'program_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(MobilityAssignment::class, 'program_id');
    }

    public function activeVersion(?string $date = null): ?MobilityPolicyVersion
    {
        $targetDate = $date ?? now()->toDateString();

        return $this->policyVersions()
            ->where('is_active', true)
            ->where('effective_from', '<=', $targetDate)
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', $targetDate);
            })
            ->orderByDesc('version_number')
            ->first();
    }
}
