<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Organization\Models\JobGrade;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobEvaluation extends Model
{
    use HasUuids;

    protected $table = 'job_evaluations';

    protected $fillable = [
        'tenant_id',
        'job_profile_id',
        'evaluation_model',
        'knowledge_score',
        'problem_solving_score',
        'accountability_score',
        'impact_score',
        'leadership_score',
        'total_points',
        'suggested_job_grade_id',
        'evaluator_user_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'knowledge_score' => 'integer',
        'problem_solving_score' => 'integer',
        'accountability_score' => 'integer',
        'impact_score' => 'integer',
        'leadership_score' => 'integer',
        'total_points' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class, 'job_profile_id');
    }

    public function suggestedJobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'suggested_job_grade_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }
}
