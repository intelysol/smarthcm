<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmRecruitmentCandidate extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_recruitment_candidates';

    protected $fillable = [
        'tenant_id',
        'candidate_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'location',
        'country',
        'source_id',
        'consent_status',
        'consent_at',
        'status',
    ];

    protected $casts = [
        'consent_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentSource::class, 'source_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(HcmRecruitmentCandidateProfile::class, 'candidate_id');
    }

    public function consents(): HasMany
    {
        return $this->hasMany(HcmRecruitmentCandidateConsent::class, 'candidate_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(HcmRecruitmentCandidateTag::class, 'candidate_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(HcmRecruitmentApplication::class, 'candidate_id');
    }
}
