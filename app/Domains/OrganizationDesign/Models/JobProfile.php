<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobProfile extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'job_profiles';

    protected $fillable = [
        'tenant_id',
        'job_id',
        'job_family_id',
        'job_sub_family_id',
        'career_track_id',
        'career_level_id',
        'job_level_id',
        'job_grade_id',
        'code',
        'title',
        'summary',
        'responsibilities',
        'requirements',
        'education_requirement',
        'experience_years_min',
        'certifications',
        'travel_requirement',
        'remote_eligibility',
        'status',
        'current_version',
    ];

    protected $casts = [
        'responsibilities' => 'array',
        'requirements' => 'array',
        'certifications' => 'array',
        'experience_years_min' => 'decimal:1',
        'current_version' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(JobFamily::class, 'job_family_id');
    }

    public function subFamily(): BelongsTo
    {
        return $this->belongsTo(JobSubFamily::class, 'job_sub_family_id');
    }

    public function careerTrack(): BelongsTo
    {
        return $this->belongsTo(CareerTrack::class, 'career_track_id');
    }

    public function careerLevel(): BelongsTo
    {
        return $this->belongsTo(CareerLevel::class, 'career_level_id');
    }

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id');
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'job_grade_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(JobProfileVersion::class, 'job_profile_id')->orderByDesc('version_number');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(JobProfileSkill::class, 'job_profile_id');
    }

    public function competencies(): HasMany
    {
        return $this->hasMany(JobProfileCompetency::class, 'job_profile_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(JobEvaluation::class, 'job_profile_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'job_id', 'job_id');
    }
}
