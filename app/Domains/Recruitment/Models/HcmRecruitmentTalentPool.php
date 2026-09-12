<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HcmRecruitmentTalentPool extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_talent_pools';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'job_family',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(
            HcmRecruitmentCandidate::class,
            'hcm_recruitment_talent_pool_candidates',
            'talent_pool_id',
            'candidate_id'
        )->withTimestamps();
    }
}
