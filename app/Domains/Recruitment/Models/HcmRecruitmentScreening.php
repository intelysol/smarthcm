<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentScreening extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_screenings';

    protected $fillable = [
        'tenant_id',
        'application_id',
        'screened_by',
        'skills_match',
        'experience_match',
        'education_match',
        'salary_match',
        'result',
        'feedback',
    ];

    protected $casts = [
        'skills_match' => 'boolean',
        'experience_match' => 'boolean',
        'education_match' => 'boolean',
        'salary_match' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentApplication::class, 'application_id');
    }

    public function screener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'screened_by');
    }
}
