<?php

namespace App\Domains\Career\Services;

use App\Domains\Analytics\Services\AnalyticsService;
use App\Domains\Career\Models\CareerJobSkillRequirement;
use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerPlanAction;
use App\Domains\Career\Models\CareerSkillGap;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Career\Models\TalentPool;
use App\Domains\Career\Models\TalentPoolMember;
use App\Domains\Career\Models\TalentReviewRecord;
use App\Domains\Employee\Models\Employee;

class CareerTalentAnalyticsService
{
    public function __construct(
        protected AnalyticsService $analytics,
        protected SuccessionRiskEngine $successionRiskEngine
    ) {}

    public function generateSnapshot(string $tenantId): array
    {
        // 1. Skill Coverage Rate
        $totalJobReqs = CareerJobSkillRequirement::query()->where('tenant_id', $tenantId)->count();
        $openGaps = CareerSkillGap::query()->where('tenant_id', $tenantId)->where('status', 'open')->count();
        $criticalGaps = CareerSkillGap::query()->where('tenant_id', $tenantId)->where('status', 'open')->where('priority', 'critical')->count();
        $skillCoverageRate = $totalJobReqs > 0 ? round(max(0, 100 - (($openGaps / $totalJobReqs) * 100)), 2) : 100.0;
        $criticalSkillGapRate = $openGaps > 0 ? round(($criticalGaps / $openGaps) * 100, 2) : 0.0;

        // 2. Career Readiness Rate (Ready Now / Total Active Plans)
        $activePlans = CareerPlan::query()->where('tenant_id', $tenantId)->whereIn('status', ['approved', 'in_progress'])->get();
        $totalActivePlans = $activePlans->count();
        $readyNowPlans = $activePlans->where('readiness_level', 'ready_now')->count();
        $careerReadinessRate = $totalActivePlans > 0 ? round(($readyNowPlans / $totalActivePlans) * 100, 2) : 0.0;

        // 3. Talent Pools & High Potential Count
        $talentPoolCount = TalentPool::query()->where('tenant_id', $tenantId)->where('status', 'active')->count();
        $highPotentialCount = TalentReviewRecord::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('nine_box_position', ['high_performance_high_potential', 'medium_performance_high_potential', 'high_performance_medium_potential'])
            ->count();

        // 4. Succession Metrics
        $successionCoverageData = $this->successionRiskEngine->calculateCoverage($tenantId);
        $avgCriticalRisk = (float) SuccessionPosition::query()->where('tenant_id', $tenantId)->avg('risk_score') ?? 0.0;

        // 5. Development Completion Rate
        $actions = CareerPlanAction::query()->where('tenant_id', $tenantId)->get();
        $totalActions = $actions->count();
        $completedActions = $actions->where('status', 'completed')->count();
        $devCompletionRate = $totalActions > 0 ? round(($completedActions / $totalActions) * 100, 2) : 100.0;

        $metrics = [
            'skill_coverage_rate' => $skillCoverageRate,
            'critical_skill_gap_rate' => $criticalSkillGapRate,
            'career_readiness_rate' => $careerReadinessRate,
            'internal_mobility_rate' => 15.0, // Baseline index
            'talent_pool_count' => $talentPoolCount,
            'high_potential_count' => $highPotentialCount,
            'succession_coverage' => $successionCoverageData['coverage_rate'],
            'ready_now_successor_rate' => $successionCoverageData['ready_now_rate'],
            'critical_position_risk' => round($avgCriticalRisk, 2),
            'development_completion_rate' => $devCompletionRate,
        ];

        $facts = [];
        foreach ($metrics as $metricKey => $val) {
            $facts[] = [
                'fact_type' => "talent.{$metricKey}",
                'fact_date' => now()->toDateString(),
                'subject_type' => 'tenant',
                'subject_id' => $tenantId,
                'dimensions' => ['domain' => 'career_talent'],
                'measures' => ['value' => (float) $val],
                'source_updated_at' => now(),
            ];
        }

        $this->analytics->ingest($tenantId, $facts);

        return $metrics;
    }
}
