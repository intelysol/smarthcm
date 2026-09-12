<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Performance\Models\Competency;
use App\Domains\Performance\Models\CompetencyLevel;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProfileCompetency extends Model
{
    use HasUuids;

    protected $table = 'job_profile_competencies';

    protected $fillable = [
        'tenant_id',
        'job_profile_id',
        'competency_id',
        'competency_level_id',
        'is_required',
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

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class, 'competency_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CompetencyLevel::class, 'competency_level_id');
    }
}
