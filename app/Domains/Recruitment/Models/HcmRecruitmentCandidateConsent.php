<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentCandidateConsent extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_candidate_consents';

    protected $fillable = [
        'tenant_id',
        'candidate_id',
        'consent_type',
        'version',
        'is_granted',
        'granted_at',
        'revoked_at',
        'retention_expiry_date',
        'is_legal_hold',
    ];

    protected $casts = [
        'is_granted' => 'boolean',
        'is_legal_hold' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'retention_expiry_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentCandidate::class, 'candidate_id');
    }
}
