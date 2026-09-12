<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProfileSkill extends Model
{
    use HasUuids;

    protected $table = 'job_profile_skills';

    protected $fillable = [
        'tenant_id',
        'job_profile_id',
        'career_skill_id',
        'is_required',
        'target_proficiency',
        'criticality',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class, 'job_profile_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(CareerSkill::class, 'career_skill_id');
    }
}
