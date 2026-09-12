<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentInterviewParticipant extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_interview_participants';

    protected $fillable = [
        'tenant_id',
        'interview_id',
        'interviewer_id',
        'role',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentInterview::class, 'interview_id');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
