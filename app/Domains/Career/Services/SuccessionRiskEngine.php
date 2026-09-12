<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Enums\SuccessionRiskLevel;
use App\Domains\Career\Events\SuccessionPositionRiskChanged;
use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Shared\Services\AuditService;

class SuccessionRiskEngine
{
    public function __construct(protected AuditService $audit) {}

    public function calculatePositionRisk(SuccessionPosition $position): array
    {
        $candidates = $position->candidates;
        $totalCandidates = $candidates->count();
        $readyNowCount = $candidates->where('readiness_timeframe', 'ready_now')->count();
        $ready1YearCount = $candidates->whereIn('readiness_timeframe', ['ready_now', 'ready_under_1_year'])->count();

        // 1. Successor Depth Risk (0 - 40 pts)
        $depthScore = match (true) {
            $totalCandidates === 0 => 40,
            $totalCandidates === 1 => 25,
            $totalCandidates === 2 => 15,
            default => 5,
        };

        // 2. Readiness Gap Risk (0 - 30 pts)
        $readinessScore = match (true) {
            $readyNowCount >= 1 => 5,
            $ready1YearCount >= 1 => 15,
            default => 30,
        };

        // 3. Criticality Factor (0 - 15 pts)
        $criticalityScore = match ($position->criticality) {
            'business_critical', 'critical' => 15,
            'leadership_critical' => 12,
            'technical_critical' => 10,
            default => 5,
        };

        // 4. Vacancy Risk Factor (0 - 15 pts)
        $vacancyScore = match ($position->vacancy_risk) {
            'critical' => 15,
            'high' => 12,
            'medium' => 8,
            default => 3,
        };

        $totalRiskScore = $depthScore + $readinessScore + $criticalityScore + $vacancyScore;

        $riskLevel = match (true) {
            $totalRiskScore >= 75 => SuccessionRiskLevel::Critical->value,
            $totalRiskScore >= 55 => SuccessionRiskLevel::High->value,
            $totalRiskScore >= 35 => SuccessionRiskLevel::Medium->value,
            default => SuccessionRiskLevel::Low->value,
        };

        return [
            'risk_score' => (float) $totalRiskScore,
            'risk_level' => $riskLevel,
            'depth_count' => $totalCandidates,
            'ready_now_count' => $readyNowCount,
            'breakdown' => [
                'depth_score' => $depthScore,
                'readiness_score' => $readinessScore,
                'criticality_score' => $criticalityScore,
                'vacancy_score' => $vacancyScore,
            ],
        ];
    }

    public function recalculatePositionRisk(SuccessionPosition $position): SuccessionPosition
    {
        $calculated = $this->calculatePositionRisk($position);
        $prevScore = $position->risk_score;

        $position->update([
            'risk_score' => $calculated['risk_score'],
        ]);

        if (abs((float) $prevScore - (float) $calculated['risk_score']) > 0.01) {
            SuccessionPositionRiskChanged::dispatch($position);
        }

        return $position;
    }

    public function calculateCoverage(string $tenantId): array
    {
        $positions = SuccessionPosition::query()
            ->where('tenant_id', $tenantId)
            ->with('candidates')
            ->get();

        $totalCritical = $positions->count();
        if ($totalCritical === 0) {
            return [
                'total_critical_positions' => 0,
                'positions_with_successors' => 0,
                'positions_with_ready_now' => 0,
                'coverage_rate' => 100.0,
                'ready_now_rate' => 100.0,
            ];
        }

        $withSuccessor = 0;
        $withReadyNow = 0;

        foreach ($positions as $pos) {
            if ($pos->candidates->isNotEmpty()) {
                $withSuccessor++;
            }
            if ($pos->candidates->contains('readiness_timeframe', 'ready_now')) {
                $withReadyNow++;
            }
        }

        $coverageRate = round(($withSuccessor / $totalCritical) * 100.0, 2);
        $readyNowRate = round(($withReadyNow / $totalCritical) * 100.0, 2);

        return [
            'total_critical_positions' => $totalCritical,
            'positions_with_successors' => $withSuccessor,
            'positions_with_ready_now' => $withReadyNow,
            'coverage_rate' => $coverageRate,
            'ready_now_rate' => $readyNowRate,
        ];
    }
}
