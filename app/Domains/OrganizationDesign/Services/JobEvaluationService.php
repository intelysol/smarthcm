<?php

namespace App\Domains\OrganizationDesign\Services;

use App\Domains\Organization\Models\JobGrade;
use App\Domains\OrganizationDesign\Models\JobEvaluation;
use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Models\User;

class JobEvaluationService
{
    /**
     * Perform multi-factor point scoring on a Job Profile.
     * Dimensions: Knowledge, Problem Solving, Accountability, Impact, Leadership.
     */
    public function evaluateJob(JobProfile $profile, array $factors, ?User $evaluator = null): JobEvaluation
    {
        $knowledge = (int) ($factors['knowledge_score'] ?? 0);
        $problemSolving = (int) ($factors['problem_solving_score'] ?? 0);
        $accountability = (int) ($factors['accountability_score'] ?? 0);
        $impact = (int) ($factors['impact_score'] ?? 0);
        $leadership = (int) ($factors['leadership_score'] ?? 0);

        $totalPoints = $knowledge + $problemSolving + $accountability + $impact + $leadership;

        // Suggested non-binding job grade mapping based on points benchmark
        $suggestedGrade = JobGrade::where('tenant_id', $profile->tenant_id)
            ->where('level', '<=', ceil($totalPoints / 100))
            ->orderByDesc('level')
            ->first();

        return JobEvaluation::create([
            'tenant_id' => $profile->tenant_id,
            'job_profile_id' => $profile->id,
            'evaluation_model' => $factors['evaluation_model'] ?? 'point_factor',
            'knowledge_score' => $knowledge,
            'problem_solving_score' => $problemSolving,
            'accountability_score' => $accountability,
            'impact_score' => $impact,
            'leadership_score' => $leadership,
            'total_points' => $totalPoints,
            'suggested_job_grade_id' => $suggestedGrade?->id,
            'evaluator_user_id' => $evaluator?->id,
            'status' => $factors['status'] ?? 'finalized',
            'notes' => $factors['notes'] ?? null,
        ]);
    }
}
