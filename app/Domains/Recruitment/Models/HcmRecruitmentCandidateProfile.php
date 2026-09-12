<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentCandidateProfile extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_candidate_profiles';

    protected $fillable = [
        'tenant_id',
        'candidate_id',
        'headline',
        'summary',
        'resume_path',
        'resume_filename',
        'current_salary',
        'expected_salary',
        'currency',
        'notice_period_days',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'skills',
        'experience_history',
        'education_history',
    ];

    protected $casts = [
        'current_salary' => 'decimal:2',
        'expected_salary' => 'decimal:2',
        'notice_period_days' => 'integer',
        'skills' => 'array',
        'experience_history' => 'array',
        'education_history' => 'array',
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
