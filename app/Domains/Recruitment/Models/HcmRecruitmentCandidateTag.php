<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentCandidateTag extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_candidate_tags';

    protected $fillable = [
        'tenant_id',
        'candidate_id',
        'tag',
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
