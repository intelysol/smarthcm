<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Models\CareerDevelopmentRecommendation;
use App\Domains\Career\Models\CareerSkillCompetencyMapping;
use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Organization\Models\Job;
use Illuminate\Support\Collection;

class CareerJobMatchingService
{
    public function __construct(
        protected CareerReadinessEngine $readinessEngine,
        protected CareerSkillGapService $skillGapService
    ) {}

    public function matchEmployeeToJob(Employee $employee, Job $job): array
    {
        $gaps = $this->skillGapService->analyzeGapsForTargetJob($employee, $job);
        $readiness = $this->readinessEngine->calculateReadiness($employee, $job);

        $missingSkills = $gaps->map(fn ($g) => [
            'skill_id' => $g->skill_id,
            'skill_name' => $g->skill?->name,
            'current_level' => $g->current_level,
            'required_level' => $g->required_level,
            'gap' => $g->gap,
            'priority' => $g->priority,
        ])->all();

        // Recommended learning based on gaps
        $gapSkillIds = $gaps->pluck('skill_id');
        $suggestedCourses = LearningCourse::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('status', 'published')
            ->limit(5)
            ->get(['id', 'code', 'title', 'duration', 'delivery_type'])
            ->toArray();

        return [
            'job_id' => $job->id,
            'job_title' => $job->title,
            'match_percentage' => $readiness['score'],
            'readiness_level' => $readiness['level'],
            'breakdown' => $readiness['breakdown'],
            'missing_skills' => $missingSkills,
            'suggested_learning' => $suggestedCourses,
        ];
    }

    public function findMatchingJobsForEmployee(Employee $employee, int $limit = 10): Collection
    {
        $jobs = Job::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('status', 'active')
            ->limit($limit)
            ->get();

        return $jobs->map(fn ($job) => $this->matchEmployeeToJob($employee, $job))
            ->sortByDesc('match_percentage')
            ->values();
    }
}
