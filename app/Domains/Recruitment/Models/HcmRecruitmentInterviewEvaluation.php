<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentInterviewEvaluation extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_interview_evaluations';

    protected $fillable = [
        'tenant_id',
        'interview_id',
        'evaluator_id',
        'technical_rating',
        'communication_rating',
        'problem_solving_rating',
        'overall_score',
        'recommendation',
        'strengths',
        'concerns',
        'confidential_notes',
        'submitted_at',
    ];

    protected $casts = [
        'technical_rating' => 'decimal:1',
        'communication_rating' => 'decimal:1',
        'problem_solving_rating' => 'decimal:1',
        'overall_score' => 'decimal:1',
        'submitted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentInterview::class, 'interview_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
