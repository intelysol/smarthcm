<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmRecruitmentJobTemplate extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_recruitment_job_templates';

    protected $fillable = [
        'tenant_id',
        'title',
        'code',
        'job_summary',
        'responsibilities',
        'required_skills',
        'preferred_skills',
        'required_experience_years',
        'education_level',
        'default_min_salary',
        'default_max_salary',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'responsibilities' => 'array',
        'required_skills' => 'array',
        'preferred_skills' => 'array',
        'required_experience_years' => 'integer',
        'default_min_salary' => 'decimal:2',
        'default_max_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requisitions(): HasMany
    {
        return $this->hasMany(HcmRecruitmentRequisition::class, 'job_template_id');
    }
}
