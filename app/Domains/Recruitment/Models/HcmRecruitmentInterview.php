<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmRecruitmentInterview extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_interviews';

    protected $fillable = [
        'tenant_id',
        'application_id',
        'title',
        'interview_type',
        'scheduled_at',
        'duration_minutes',
        'location',
        'meeting_url',
        'status',
        'internal_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentApplication::class, 'application_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(HcmRecruitmentInterviewParticipant::class, 'interview_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(HcmRecruitmentInterviewEvaluation::class, 'interview_id');
    }
}
